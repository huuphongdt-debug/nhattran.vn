// Run only against the isolated HTTP test server; Edge CDP must listen on localhost:9231.
import fs from 'node:fs';
const [base, secret, output] = process.argv.slice(2);
const target = await (await fetch('http://127.0.0.1:9231/json/new?about:blank', {method:'PUT'})).json();
const socket = new WebSocket(target.webSocketDebuggerUrl);
await new Promise(resolve => socket.addEventListener('open', resolve, {once:true}));
let id=0; const pending=new Map();
socket.addEventListener('message', event=>{const r=JSON.parse(event.data);if(pending.has(r.id)){const p=pending.get(r.id);pending.delete(r.id);r.error?p.reject(r.error):p.resolve(r.result);}});
function call(method,params={}) {return new Promise((resolve,reject)=>{const key=++id;pending.set(key,{resolve,reject});socket.send(JSON.stringify({id:key,method,params}));});}
const evaluate=async expression=>(await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true})).result.value;
const results=[];
try {
 await call('Page.enable'); await call('Network.enable');
 await call('Network.setCookie',{name:'layout_test',value:secret,url:base});
 for (const width of [390,768,1440]) {
  await call('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width===390});
  for (const page of ['admin/dashboard.php','admin/products/products.php','admin/projects/projects.php','admin/services/services.php','admin/posts/posts.php','admin/banners/banners.php','admin/account/password.php','public/index.php','public/products.php','public/about.php','public/contact.php']) {
   await call('Page.navigate',{url:base+'/'+page});
   await new Promise(resolve=>setTimeout(resolve,1400));
   const metrics=await evaluate('({width:innerWidth,scroll:document.documentElement.scrollWidth,title:document.title,ready:document.readyState})');
   const entry={page,requestedWidth:width,...metrics};
   if(page.startsWith('admin/') && width<1000) {
    entry.menuOpened=await evaluate("document.querySelector('.admin-menu-button').click();document.querySelector('.admin-menu-button').getAttribute('aria-expanded')==='true'");
    await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});
    entry.menuClosed=await evaluate("document.querySelector('.admin-menu-button').getAttribute('aria-expanded')==='false'");
   }
   results.push(entry);
   const shot=await call('Page.captureScreenshot',{format:'png'});
   fs.writeFileSync(output+'/'+page.replaceAll('/','-')+'-'+width+'.png',Buffer.from(shot.data,'base64'));
  }
 }
 fs.writeFileSync(output+'/browser-results.json',JSON.stringify(results,null,2));
 console.log(JSON.stringify(results));
 if (results.some(r=>r.scroll>r.requestedWidth+1 || r.width>r.requestedWidth+1 || r.ready!=='complete' || r.menuOpened===false || r.menuClosed===false)) process.exitCode=1;
} finally {await call('Page.close').catch(()=>{});socket.close();}
