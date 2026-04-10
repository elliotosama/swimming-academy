<?php


class SettingController {
  public function settings(): void {
    auth_require(['admin']);
    $db       = get_db();
    $rows     = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    $this->renderView('admin/settings', [
        'pageTitle' => 'إعدادات النظام',
        'settings'  => $rows,
        'saved'     => false,
    ]);
}

public function saveSettings(): void {
    auth_require(['admin']);
    $db       = get_db();
    $input    = $_POST['settings'] ?? [];
    $allowed  = ['min_payment_amount'];   // whitelist

    foreach ($allowed as $key) {
        if (!array_key_exists($key, $input)) continue;
        $val  = (float) $input[$key];
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value, updated_by, updated_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                updated_by    = VALUES(updated_by),
                updated_at    = NOW()
        ");
        $stmt->execute([$key, $val, auth_user()['id']]);
    }

    log_action('updated_settings', json_encode($input), auth_user()['id']);

    $rows = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    $this->renderView('admin/settings', [
        'pageTitle' => 'إعدادات النظام',
        'settings'  => $rows,
        'saved'     => true,
    ]);
}
}