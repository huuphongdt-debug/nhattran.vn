<?php
/*
 * Kiểm tra tích hợp model nhóm hãng trên database cấu hình hiện tại.
 * Tạo dữ liệu thử trong transaction và rollback ở finally; không dùng như migration.
 */

// Integration check against the configured database; fixtures are rolled back.
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/Models/ManufacturerGroup.php';
function expectGroup(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
$pdo->beginTransaction();
try {
    $suffix = bin2hex(random_bytes(8));
    $pdo->prepare('INSERT INTO manufacturers(name,slug) VALUES (?,?)')->execute(['Test '.$suffix, 'test-'.$suffix]);
    $manufacturer = (int) $pdo->lastInsertId();
    $groups = [];
    foreach ([1, 2] as $number) {
        $pdo->prepare('INSERT INTO manufacturer_groups(name) VALUES (?)')->execute(['Test '.$suffix.' '.$number]);
        $groups[] = (int) $pdo->lastInsertId();
    }
    $model = new ManufacturerGroup($pdo);
    $directoryIds = array_column(array_merge(...array_column($model->directory(), 'brands')), 'id');
    expectGroup(in_array($manufacturer, array_map('intval', $directoryIds), true), 'Ungrouped manufacturer without logo missing');
    $model->sync($manufacturer, [$groups[0], $groups[1], $groups[0]]);
    $appearances = 0;
    foreach ($model->directory() as $section) {
        foreach ($section['brands'] as $brand) if ((int) $brand['id'] === $manufacturer) $appearances++;
    }
    expectGroup($appearances === 2, 'Public directory missing multi-group manufacturer');
    expectGroup(count($model->memberships()[$manufacturer]) === 2, 'Multi-group assignment failed');
    try {
        $model->sync($manufacturer, [-1]);
        throw new RuntimeException('Invalid group accepted');
    } catch (InvalidArgumentException $expected) {}
    expectGroup(count($model->memberships()[$manufacturer]) === 2, 'Invalid input changed memberships');
    $model->sync($manufacturer, [$groups[0]]);
    expectGroup(count($model->memberships()[$manufacturer]) === 1, 'Reassignment failed');
    $pdo->prepare('DELETE FROM manufacturer_groups WHERE id=?')->execute([$groups[0]]);
    expectGroup(empty($model->memberships()[$manufacturer]), 'Membership cascade failed');
    expectGroup((bool) $pdo->query('SELECT id FROM manufacturers WHERE id='.$manufacturer)->fetchColumn(), 'Deleting group removed manufacturer');
    echo "Manufacturer group integration checks passed. Fixtures rolled back.\n";
} finally {
    $pdo->rollBack();
}
