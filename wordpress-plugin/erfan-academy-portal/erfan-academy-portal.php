<?php
/**
 * Plugin Name: Erfan Academy Portal
 * Description: Bilingual learning portal with separate student, teacher, administrator and support dashboards.
 * Version: 1.0.0
 * Author: Erfan Tooltips
 * Text Domain: erfan-academy-portal
 */
if (!defined('ABSPATH')) exit;

final class Erfan_Academy_Portal {
 const VERSION = '1.0.0';
 const SHORTCODE = 'erfan_academy_portal';
 public function __construct() {
  add_action('init', [$this,'register_roles']);
  add_shortcode(self::SHORTCODE, [$this,'render_portal']);
  add_action('admin_menu', [$this,'admin_menu']);
  add_action('admin_post_eap_save_settings', [$this,'save_settings']);
  add_action('wp_enqueue_scripts', [$this,'assets']);
 }
 public function register_roles() {
  add_role('eap_student', 'دانش‌آموز', ['read'=>true]);
  add_role('eap_teacher', 'مدرس', ['read'=>true, 'upload_files'=>true]);
  add_role('eap_support', 'پشتیبانی', ['read'=>true]);
 }
 public function assets() {
  if (!is_singular()) return;
  global $post;
  if (!$post || !has_shortcode($post->post_content, self::SHORTCODE)) return;
  wp_register_style('eap-portal', false, [], self::VERSION);
  wp_enqueue_style('eap-portal');
  wp_add_inline_style('eap-portal', $this->css());
  wp_register_script('eap-portal', '', [], self::VERSION, true);
  wp_enqueue_script('eap-portal');
  wp_add_inline_script('eap-portal', $this->js());
 }
 private function css() {
 return '.eap{--navy:#111d38;--blue:#425ff5;--teal:#18b6aa;direction:rtl;font-family:inherit;color:#18233d;background:#f4f6fb;border-radius:22px;overflow:hidden;display:grid;grid-template-columns:245px 1fr;min-height:690px;box-shadow:0 18px 55px #17264a18}.eap *{box-sizing:border-box}.eap-side{background:var(--navy);color:#fff;padding:24px 16px}.eap-brand{font-size:19px;font-weight:800;padding:8px 10px 24px}.eap-brand small{display:block;font-size:11px;color:#aab7d2;font-weight:400}.eap-nav{display:grid;gap:6px;margin-top:22px}.eap-nav button{background:transparent;border:0;color:#cbd5e8;text-align:right;padding:12px;border-radius:10px;cursor:pointer;font:inherit;font-size:13px}.eap-nav button.active,.eap-nav button:hover{background:#ffffff19;color:white}.eap-main{padding:25px;min-width:0}.eap-top{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:24px}.eap-muted{color:#7e89a0;font-size:12px}.eap-title{font-size:25px;font-weight:800;margin:0 0 7px}.eap-switch{display:flex;gap:6px;flex-wrap:wrap}.eap button,.eap input,.eap select{font:inherit}.eap-btn{border:0;background:var(--blue);color:#fff;padding:10px 15px;border-radius:10px;cursor:pointer}.eap-btn.secondary{background:#edf0ff;color:var(--blue)}.eap-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}.eap-card{background:#fff;border:1px solid #e8ebf3;border-radius:15px;padding:17px;min-width:0}.eap-card small{display:block;color:#7e89a0;font-size:11px}.eap-number{font-size:26px;font-weight:800;margin-top:8px}.eap-lower{display:grid;grid-template-columns:1.4fr 1fr;gap:14px;margin-top:15px}.eap-row{display:flex;justify-content:space-between;gap:10px;padding:12px 0;border-bottom:1px solid #edf0f5;font-size:13px}.eap-row:last-child{border:0}.eap-progress{height:6px;background:#edf0f6;border-radius:9px;margin-top:7px;overflow:hidden}.eap-progress i{display:block;height:100%;background:linear-gradient(90deg,var(--blue),var(--teal))}.eap-auth{max-width:460px;margin:35px auto;background:#fff;border:1px solid #e8ebf3;border-radius:18px;padding:25px}.eap-field{display:grid;gap:6px;margin:13px 0}.eap-field label{font-size:12px;color:#58637b}.eap-field input,.eap-field select{width:100%;border:1px solid #dfe4ef;border-radius:10px;padding:12px;background:#fff}.eap-alert{padding:12px;border-radius:10px;background:#fff4df;color:#79520b;margin:12px 0;font-size:12px}.eap-lang{border:1px solid #e2e7f0;background:white;border-radius:9px;padding:7px 10px;cursor:pointer}.eap-table{width:100%;border-collapse:collapse;font-size:12px}.eap-table td,.eap-table th{text-align:right;padding:11px;border-bottom:1px solid #edf0f5}.eap-table th{color:#7e89a0;font-weight:500}.eap-hidden{display:none!important}@media(max-width:850px){.eap{grid-template-columns:1fr}.eap-side{padding:14px}.eap-brand{padding:4px}.eap-nav{display:flex;overflow:auto;margin-top:10px}.eap-nav button{white-space:nowrap}.eap-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.eap-lower{grid-template-columns:1fr}}@media(max-width:480px){.eap-main{padding:15px}.eap-title{font-size:20px}.eap-grid{gap:8px}.eap-card{padding:12px}}';
 }
 private function js() {
 return "document.addEventListener('click',function(e){var b=e.target.closest('[data-eap-lang]');if(!b)return;var root=b.closest('.eap');var en=b.dataset.eapLang==='en';root.querySelectorAll('[data-fa][data-en]').forEach(function(n){n.textContent=en?n.dataset.en:n.dataset.fa});root.dir=en?'ltr':'rtl';root.querySelectorAll('[data-eap-lang]').forEach(function(x){x.classList.toggle('active',x===b)});});";
 }
 private function t($fa,$en) { return '<span data-fa="'.esc_attr($fa).'" data-en="'.esc_attr($en).'">'.esc_html($fa).'</span>'; }
 public function render_portal() {
  if (!is_user_logged_in()) return $this->auth_view();
  $u=wp_get_current_user(); $role=$this->user_role($u);
  $menus=[
   'eap_student'=>['نمای کلی','دوره‌های من','کلاس‌های آنلاین','آزمون‌ها و تکالیف','پیشرفت تحصیلی','گواهی‌ها','پرداخت‌ها','پشتیبانی'],
   'eap_teacher'=>['نمای کلی','مدیریت دوره‌ها','کلاس‌های زنده','هنرجویان','حضور و غیاب','تکالیف و آزمون‌ها','گزارش عملکرد','پیام‌ها'],
   'administrator'=>['نمای کلی','مدیریت کاربران','مدیریت مدرس‌ها','دوره‌ها','مالی و پرداخت‌ها','گزارش‌ها','تنظیمات','امنیت و دسترسی'],
   'eap_support'=>['نمای کلی','تیکت‌های جدید','در انتظار پاسخ','گفت‌وگوها','مشکلات پرداخت','راهنمای کاربران','گزارش پاسخ‌گویی']
  ];
  $menu=$menus[$role]??$menus['eap_student'];
  $labels=['eap_student'=>['داشبورد دانش‌آموز','Student dashboard'],'eap_teacher'=>['داشبورد مدرس','Teacher dashboard'],'administrator'=>['پنل مدیریت','Administration'],'eap_support'=>['مرکز پشتیبانی','Support center']];
  $title=$labels[$role]??$labels['eap_student'];
  ob_start(); ?>
  <div class="eap" dir="rtl">
   <aside class="eap-side"><div class="eap-brand">Erfan Tooltips<small>آکادمی آموزش هوشمند</small></div><div class="eap-muted" style="color:#8e9dbb">منوی اختصاصی</div><nav class="eap-nav"><?php foreach($menu as $i=>$item): ?><button type="button" class="<?php echo $i===0?'active':''; ?>" data-eap-menu="<?php echo esc_attr($item); ?>"><?php echo esc_html($item); ?></button><?php endforeach; ?></nav><p style="font-size:11px;color:#91a0bd;margin-top:35px">© Erfan Tooltips</p></aside>
   <main class="eap-main"><header class="eap-top"><div><h2 class="eap-title"><?php echo esc_html($title[0]); ?></h2><div class="eap-muted"><?php echo esc_html($u->display_name); ?>، خوش آمدی.</div></div><div><button class="eap-lang" data-eap-lang="fa">فارسی</button> <button class="eap-lang" data-eap-lang="en">English</button> <a class="eap-btn secondary" href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>">خروج</a></div></header>
   <section class="eap-grid"><?php
    $stats=$role==='eap_teacher'? [['هنرجویان فعال','Active learners','—'],['دوره‌های من','My courses','—'],['جلسات این هفته','Sessions this week','—'],['درآمد','Revenue','—']] : ($role==='administrator'?[['کل کاربران','Total users',count_users()['total_users']],['مدرس‌ها','Teachers','—'],['دوره‌ها','Courses','—'],['تیکت‌های باز','Open tickets','—']]:($role==='eap_support'?[['تیکت‌های جدید','New tickets','—'],['در انتظار پاسخ','Awaiting reply','—'],['حل‌شده امروز','Resolved today','—'],['رضایت کاربران','User satisfaction','—']]:[['دوره‌های فعال','Active courses','—'],['کلاس‌های پیش‌رو','Upcoming classes','—'],['تکالیف باقی‌مانده','Pending tasks','—'],['پیشرفت کلی','Overall progress','—']]));
    foreach($stats as $s): ?><div class="eap-card"><small><?php echo $this->t($s[0],$s[1]); ?></small><div class="eap-number"><?php echo esc_html($s[2]); ?></div></div><?php endforeach; ?>
   </section><section class="eap-lower"><div class="eap-card"><h3><?php echo $this->t('فضای کاری من','My workspace'); ?></h3><div class="eap-row"><span><?php echo $this->t('مدیریت و مشاهده اطلاعات','Manage and view your information'); ?></span><span>›</span></div><div class="eap-row"><span><?php echo $this->t('فعالیت‌های اخیر','Recent activity'); ?></span><span>›</span></div><div class="eap-row"><span><?php echo $this->t('تنظیمات حساب','Account settings'); ?></span><span>›</span></div></div><div class="eap-card"><h3><?php echo $this->t('دسترسی سریع','Quick access'); ?></h3><p class="eap-muted"><?php echo $this->t('امکانات هر نقش به‌صورت جداگانه نمایش داده می‌شود.','Features are separated by user role.'); ?></p><a class="eap-btn" href="<?php echo esc_url(admin_url('profile.php')); ?>"><?php echo $this->t('ویرایش حساب','Edit profile'); ?></a></div></section>
   <div class="eap-alert"><?php echo $this->t('این نسخه زیرساخت پنل و نقش‌ها را فراهم می‌کند؛ مدیریت واقعی دوره، پرداخت و کلاس زنده در مرحله اتصال به سامانه‌های مربوطه فعال می‌شود.','This release provides the portal and role foundation. Course operations, payments and live classes require integration with their respective services.'); ?></div></main></div>
  <?php return ob_get_clean();
 }
 private function user_role($user) {
  foreach(['administrator','eap_teacher','eap_support','eap_student'] as $r) if(in_array($r,(array)$user->roles,true)) return $r;
  return 'eap_student';
 }
 private function auth_view() {
  $msg='';
  if(isset($_POST['eap_action']) && $_POST['eap_action']==='login' && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])),'eap_login')) {
   $creds=['user_login'=>sanitize_text_field(wp_unslash($_POST['username']??'')),'user_password'=>wp_unslash($_POST['password']??''),'remember'=>!empty($_POST['remember'])];
   $user=wp_signon($creds,is_ssl());
   if(is_wp_error($user)) $msg='اطلاعات ورود صحیح نیست. / Invalid login details.';
   else { wp_safe_redirect(get_permalink()); exit; }
  }
  if(isset($_POST['eap_action']) && $_POST['eap_action']==='register' && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])),'eap_register')) {
   $email=sanitize_email(wp_unslash($_POST['email']??'')); $username=sanitize_user(wp_unslash($_POST['username']??'')); $pass=(string)wp_unslash($_POST['password']??'');
   if(!get_option('users_can_register')) $msg='ثبت‌نام عمومی در تنظیمات وردپرس غیرفعال است. / Public registration is disabled.';
   elseif(!$email || !is_email($email) || !$username || strlen($pass)<12) $msg='ایمیل، نام کاربری معتبر و رمز حداقل ۱۲ کاراکتری لازم است. / Valid email, username and 12-character password required.';
   elseif(username_exists($username)||email_exists($email)) $msg='نام کاربری یا ایمیل قبلاً ثبت شده است. / Username or email already exists.';
   else { $id=wp_create_user($username,$pass,$email); if(is_wp_error($id)) $msg='ثبت‌نام انجام نشد. / Registration failed.'; else { $u=new WP_User($id); $u->set_role('eap_student'); $msg='حساب ساخته شد؛ اکنون وارد شوید. / Account created. Please sign in.'; } }
  }
  ob_start(); ?><div class="eap" dir="rtl"><main class="eap-main"><div class="eap-auth"><div class="eap-brand" style="color:#17233e">Erfan Tooltips<small style="color:#7e89a0">آکادمی آموزش هوشمند</small></div><div class="eap-top"><h2><?php echo $this->t('ورود به حساب','Sign in'); ?></h2><div><button class="eap-lang" data-eap-lang="fa">فا</button> <button class="eap-lang" data-eap-lang="en">EN</button></div></div><?php if($msg): ?><div class="eap-alert"><?php echo esc_html($msg); ?></div><?php endif; ?>
  <form method="post"><input type="hidden" name="eap_action" value="login"><?php wp_nonce_field('eap_login'); ?><div class="eap-field"><label><?php echo $this->t('نام کاربری یا ایمیل','Username or email'); ?></label><input name="username" required autocomplete="username"></div><div class="eap-field"><label><?php echo $this->t('رمز عبور','Password'); ?></label><input type="password" name="password" required autocomplete="current-password"></div><label style="font-size:12px"><input type="checkbox" name="remember" value="1"> <?php echo $this->t('مرا به خاطر بسپار','Remember me'); ?></label><p><button class="eap-btn" type="submit"><?php echo $this->t('ورود','Sign in'); ?></button></p></form>
  <hr style="border:0;border-top:1px solid #edf0f5;margin:20px 0"><h3><?php echo $this->t('ساخت حساب دانش‌آموزی','Create student account'); ?></h3><form method="post"><input type="hidden" name="eap_action" value="register"><?php wp_nonce_field('eap_register'); ?><div class="eap-field"><label><?php echo $this->t('نام کاربری','Username'); ?></label><input name="username" required autocomplete="username"></div><div class="eap-field"><label><?php echo $this->t('ایمیل','Email'); ?></label><input type="email" name="email" required autocomplete="email"></div><div class="eap-field"><label><?php echo $this->t('رمز عبور (حداقل ۱۲ نویسه)','Password (12 characters minimum)'); ?></label><input type="password" name="password" minlength="12" required autocomplete="new-password"></div><button class="eap-btn secondary" type="submit"><?php echo $this->t('ثبت‌نام دانش‌آموز','Register as student'); ?></button></form><p class="eap-muted"><?php echo $this->t('حساب مدرس و پشتیبان فقط توسط مدیر ساخته یا ارتقا داده می‌شود.','Teacher and support accounts are assigned by an administrator.'); ?></p></div></main></div><?php return ob_get_clean();
 }
 public function admin_menu() { add_menu_page('Erfan Academy Portal','Erfan Academy','manage_options','eap-settings',[$this,'settings_page'],'dashicons-welcome-learn-more',58); }
 public function settings_page() {
  if(!current_user_can('manage_options')) return;
  echo '<div class="wrap"><h1>Erfan Academy Portal</h1><p>برای نمایش پرتال، برگه‌ای بسازید و شورت‌کد زیر را در آن قرار دهید:</p><code>['.esc_html(self::SHORTCODE).']</code><h2>مدیریت نقش‌ها</h2><p>برای تعیین نقش کاربر به مسیر کاربران ← همه کاربران بروید. نقش‌های افزونه: eap_student، eap_teacher و eap_support.</p><p>ثبت‌نام عمومی وردپرس را در تنظیمات ← عمومی فعال کنید تا ثبت‌نام دانش‌آموزی فعال شود.</p><p><strong>نکته امنیتی:</strong> مدیر و پشتیبان/مدرس باید فقط از سوی مدیر سایت ایجاد شوند.</p></div>';
 }
 public function save_settings() { if(!current_user_can('manage_options')) wp_die('Forbidden'); }
}
new Erfan_Academy_Portal();
