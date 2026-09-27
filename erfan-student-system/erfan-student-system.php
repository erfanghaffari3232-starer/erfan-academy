<?php
/**
 * Plugin Name: Erfan Student System
 * Description: پنل دانش‌آموز عرفان با ورود، ثبت‌نام و بخش کلاس آنلاین.
 * Version: 3.0.0
 * Author: Erfan
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH')) exit;

final class Erfan_Student_System {
 const V='3.0.0';
 static function init(){
  add_shortcode('erfan_login',[__CLASS__,'login']);
  add_shortcode('erfan_register',[__CLASS__,'register']);
  add_shortcode('erfan_panel',[__CLASS__,'panel']);
  add_action('wp_enqueue_scripts',[__CLASS__,'assets']);
  add_action('template_redirect',[__CLASS__,'protect']);
  add_action('init',[__CLASS__,'forms']);
 }
 static function assets(){
  wp_register_style('erfan-student',false,[],self::V); wp_enqueue_style('erfan-student');
  wp_add_inline_style('erfan-student','
  .erfan-panel,.erfan-auth{direction:rtl}.erfan-panel{max-width:1050px;margin:35px auto;padding:20px}
  .erfan-hero{display:flex;align-items:center;gap:20px;padding:30px;border-radius:24px;background:linear-gradient(135deg,#111827,#2563eb);color:#fff}
  .erfan-hero .icon{font-size:52px}.erfan-hero h1{margin:4px 0}.erfan-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;margin-top:20px}
  .erfan-card{background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:22px;box-shadow:0 8px 25px rgba(0,0,0,.06)}
  .erfan-card h3{margin-top:0}.erfan-btn{display:inline-block;padding:11px 17px;border-radius:11px;background:#2563eb;color:#fff!important;text-decoration:none!important;font-weight:700}
  .erfan-live{border:1px solid #bfdbfe;background:#eff6ff}.erfan-ready{color:#15803d;font-weight:700}
  .erfan-muted{color:#6b7280}.erfan-auth{max-width:520px;margin:50px auto;padding:20px}.erfan-auth-card{background:#fff;border:1px solid #e5e7eb;border-radius:22px;padding:30px}.erfan-auth input{width:100%;box-sizing:border-box;padding:12px;margin:7px 0 12px;border:1px solid #d1d5db;border-radius:10px}.erfan-auth button{width:100%;padding:13px;border:0;border-radius:10px;background:#111827;color:#fff;font-weight:700}.erfan-error{background:#fee2e2;padding:10px;border-radius:10px;color:#991b1b}
  @media(max-width:650px){.erfan-hero{flex-direction:column;text-align:center}}
  ');
 }
 static function page($slug){$p=get_page_by_path($slug);return $p?get_permalink($p):home_url('/'.$slug.'/');}
 static function activate(){
  $pages=['erfan-login'=>['ورود عرفان','[erfan_login]'],'erfan-register'=>['ثبت‌نام عرفان','[erfan_register]'],'erfan-panel'=>['پنل دانش‌آموز عرفان','[erfan_panel]']];
  foreach($pages as $slug=>$d) if(!get_page_by_path($slug)) wp_insert_post(['post_title'=>$d[0],'post_name'=>$slug,'post_content'=>$d[1],'post_status'=>'publish','post_type'=>'page']);
  flush_rewrite_rules();
 }
 static function protect(){if(is_page('erfan-panel')&&!is_user_logged_in()){wp_safe_redirect(self::page('erfan-login'));exit;}}
 static function forms(){
  if($_SERVER['REQUEST_METHOD']!=='POST'||empty($_POST['erfan_action'])) return;
  if(empty($_POST['erfan_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['erfan_nonce'])),'erfan_forms')) return;
  $a=sanitize_key(wp_unslash($_POST['erfan_action']));
  if($a==='login'){
   $u=wp_signon(['user_login'=>sanitize_text_field(wp_unslash($_POST['username']??'')),'user_password'=>(string)($_POST['password']??''),'remember'=>true],is_ssl());
   if(!is_wp_error($u)){wp_safe_redirect(self::page('erfan-panel'));exit;}
  }
  if($a==='register'){
   $un=sanitize_user(wp_unslash($_POST['username']??''));$em=sanitize_email(wp_unslash($_POST['email']??''));$pw=(string)($_POST['password']??'');
   if(!$un||!$em||!$pw||username_exists($un)||email_exists($em))return;
   $id=wp_create_user($un,$pw,$em); if(!is_wp_error($id)){wp_update_user(['ID'=>$id,'role'=>'subscriber']);wp_set_auth_cookie($id,true);wp_safe_redirect(self::page('erfan-panel'));exit;}
  }
 }
 static function bbb_url(){
  if(!is_user_logged_in()) return '';
  $url=apply_filters('erfan_student_bbb_join_url','',wp_get_current_user());
  return is_string($url)?esc_url_raw($url):'';
 }
 static function login(){
  if(is_user_logged_in()) return '<div class="erfan-card">وارد حساب هستید. <a href="'.esc_url(self::page('erfan-panel')).'">پنل من</a></div>';
  ob_start();?><div class="erfan-auth"><div class="erfan-auth-card"><h1>🎓 ورود به عرفان</h1><form method="post"><?php wp_nonce_field('erfan_forms','erfan_nonce');?><input type="hidden" name="erfan_action" value="login"><input name="username" placeholder="نام کاربری یا ایمیل" required><input type="password" name="password" placeholder="رمز عبور" required><button>ورود به پنل</button></form><p>حساب ندارید؟ <a href="<?php echo esc_url(self::page('erfan-register'));?>">ثبت‌نام</a></p></div></div><?php return ob_get_clean();
 }
 static function register(){
  if(is_user_logged_in()) return '<div class="erfan-card">وارد حساب هستید.</div>';
  ob_start();?><div class="erfan-auth"><div class="erfan-auth-card"><h1>✨ ثبت‌نام دانش‌آموز</h1><form method="post"><?php wp_nonce_field('erfan_forms','erfan_nonce');?><input type="hidden" name="erfan_action" value="register"><input name="username" placeholder="نام کاربری" required><input type="email" name="email" placeholder="ایمیل" required><input type="password" name="password" placeholder="رمز عبور" required><button>ساخت حساب</button></form><p>حساب دارید؟ <a href="<?php echo esc_url(self::page('erfan-login'));?>">ورود</a></p></div></div><?php return ob_get_clean();
 }
 static function panel(){
  if(!is_user_logged_in()) return '';
  $u=wp_get_current_user(); $bbb=self::bbb_url();
  ob_start();?><div class="erfan-panel"><div class="erfan-hero"><div class="icon">🎓</div><div><small>خوش آمدی</small><h1><?php echo esc_html($u->display_name?:$u->user_login);?> 👋</h1><p>پنل دانش‌آموز</p></div></div><div class="erfan-grid">
  <div class="erfan-card"><h3>📚 دوره‌های من</h3><p class="erfan-muted">دوره‌های اختصاص داده‌شده در این قسمت نمایش داده می‌شوند.</p></div>
  <div class="erfan-card erfan-live"><h3>🎥 کلاس آنلاین</h3><?php if($bbb):?><p class="erfan-ready">🟢 کلاس آماده است</p><a class="erfan-btn" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($bbb);?>">🎥 ورود به کلاس</a><?php else:?><p class="erfan-muted">پس از اختصاص کلاس، لینک ورود به BigBlueButton اینجا نمایش داده می‌شود.</p><?php endif;?></div>
  <div class="erfan-card"><h3>🏆 مسابقات</h3><p class="erfan-muted">مسابقات و چالش‌ها در آینده اینجا قرار می‌گیرند.</p></div>
  </div><p><a href="<?php echo esc_url(wp_logout_url(home_url('/')));?>">خروج از حساب</a></p></div><?php return ob_get_clean();
 }
}
Erfan_Student_System::init(); register_activation_hook(__FILE__,['Erfan_Student_System','activate']);
