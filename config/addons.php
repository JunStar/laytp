<?php
return array (
  'api_url' => 'https://api.laytp.com',
  'aliyun_mobileauth' => 
  array (
    'access_key_id' => '',
    'access_key_secret' => '',
  ),
  'aliyun_mobilemsg' => 
  array (
    'access_key_id' => '',
    'access_key_secret' => '',
    'sign' => '',
    'template' => 
    array (
      'reg_login' => '阿里云的模板ID',
    ),
    'expire_time' => '',
    'interval_time' => '',
  ),
  'email' => 
  array (
    'send_type' => 
    array (
      'smtp' => 'SMTP',
      'mail' => 'Mail',
    ),
    'smtp_host' => 'smtp.qq.com',
    'smtp_port' => '587',
    'smtp_user' => '',
    'smtp_password' => '',
    'verify_type' => 
    array (
      'none' => 'None',
      'tls' => 'TLS',
      'ssl' => 'SSL',
    ),
    'from' => '',
    'from_name' => '',
    'max_send_num' => '3',
  ),
  'aliyun_oss' => 
  array (
    'open_status' => 
    array (
      'close' => '关闭',
      'open' => '打开',
    ),
    'access_key' => '',
    'secret_key' => '',
    'endpoint' => '',
    'bucket' => '',
    'domain' => '',
  ),
  'qiniu_kodo' => 
  array (
    'open_status' => 
    array (
      'close' => '关闭',
      'open' => '打开',
    ),
    'access_key' => '',
    'secret_key' => '',
    'bucket' => '',
    'domain' => '',
  ),
  'autocreate' => 
  array (
    'special_fields' => 
    array (
      0 => 'id',
    ),
    'system_tables' => 
    array (
      0 => 'lt_admin_log',
      1 => 'lt_admin_menu',
      2 => 'lt_admin_role',
      3 => 'lt_admin_role_rel_menu',
      4 => 'lt_admin_role_rel_user',
      5 => 'lt_autocreate_curd',
      6 => 'lt_sysconf',
      7 => 'lt_autocreate_menu',
      8 => 'lt_user_token',
      9 => 'lt_attachment',
    ),
  ),
);