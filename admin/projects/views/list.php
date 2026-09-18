<?php /*
 * Danh sách dự án dùng $projects và $token từ projects-data.php.
 * Mỗi biểu mẫu xóa gửi action=delete, ID và CSRF; projects.js hỗ trợ xác nhận.
 * Liên kết xem ngoài website chỉ xuất hiện với dự án đã công bố.
 */
?><div class="project-panel"><p><?= count($projects) ?> dự án</p>
    <?php if (!$projects): ?><div class="project-empty"><h2>Chưa có dự án</h2><p>Thêm hồ sơ đầu tiên và chọn “Công bố trên website” để hiển thị ngoài trang Dự án.</p><a class="project-button" href="projects.php?new=1">Thêm dự án đầu tiên</a></div>
    <?php else: ?><div class="project-table-wrap"><table><thead><tr><th>Dự án</th><th>Khách hàng / Địa điểm</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
    <?php foreach ($projects as $project): ?><tr>
        <td><strong><?= projectEscape($project['name']) ?></strong><?php if ($project['featured']): ?><small class="project-badge">Nổi bật</small><?php endif; ?></td>
        <td><?= projectEscape($project['customer']) ?><br><small><?= projectEscape($project['location']) ?></small></td>
        <td><?= $project['status'] ? 'Đã công bố' : 'Bản nháp' ?></td>
        <td><div class="actions"><a class="btn btn-edit" href="projects.php?id=<?= (int) $project['id'] ?>"><?= adminIcon('edit') ?> Sửa</a><?php if ($project['status']): ?><a class="btn btn-secondary" href="../../public/project-detail.php?id=<?= (int) $project['id'] ?>" target="_blank" rel="noopener"><?= adminIcon('external') ?> Xem</a><?php endif; ?>
        <form method="post" data-delete-project><input type="hidden" name="csrf" value="<?= projectEscape($token) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $project['id'] ?>"><button class="btn btn-delete" type="submit"><?= adminIcon('trash') ?> Xóa</button></form></div></td>
    </tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
