<?php
/**
 * Plugin Name: Erfan Academy Online
 * Description: Online-only learning platform: courses, enrollment, role dashboards and integration settings.
 * Version: 2.0.0
 * Author: Erfan Tooltips
 * Text Domain: erfan-academy-online
 */
if (!defined('ABSPATH')) exit;

final class Erfan_Academy_Online {
 const VERSION='2.0.0';
 public function __construct(){
  add_action('init',[$this,'roles']);
  add_action('init',[$this,'types']);
  add_action('add_meta_boxes',[$this,'meta_boxes']);
  add_action('save_post_eao_course',[$this,'save_course'],10,2);
  add_shortcode('erfan_academy_portal',[$this,'portal']);
  add_shortcode('erfan_academy_courses',[$this,'catalog']);
  add_shortcode('erfan_academy_my_courses',[$this,'my_courses']);
  add_action('init',[$this,'handle_forms']);
  add_action('admin_menu',[$this,'admin_menu']);
  add_action('admin_init',[$this,'settings']);
  add_action('wp_enqueue_scripts',[$this,'assets']);
 }
 public function roles(){
  add_role('eao_student','دانش‌آموز',['read'=>true]);
  add_role('eao_teacher','مدرس',['read'=>true, 'upload_files'=>true]);
  add_role('eao_support','پشتیبانی',['read'=>true]);
 }
 public function types(){
  register_post_type('eao_course',['labels'=>['name'=>'دوره‌های آنلاین','singular_name'=>'دوره آنلاین','add_new_item'=>'افزودن دوره آنلاین'],'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-video-alt3','supports'=>['title','editor','thumbnail','author'],'has_archive'=>true,'rewrite'=>['slug'=>'online-courses']]);
  register_post_type('eao_booking',['labels'=>['name'=>'ثبت‌نام‌ها','singular_name'=>'ثبت‌نام'],'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-clipboard','supports'=>['title','author']]);
 }
 public function meta_boxes(){add_meta_box('eao_course_info','اطلاعات دوره آنلاین',[$this,'course_box'],'eao_course','normal','high');}
 public function course_box($post){
  wp_nonce_field('eao_save_course','eao_course_nonce');
  $fields=['level'=>'سطح دوره','price'=>'قیمت (تومان)','capacity'=>'ظرفیت','start'=>'تاریخ شروع','end'=>'تاریخ پایان','schedule'=>'روز و ساعت برگزاری','teacher'=>'نام مدرس','meeting'=>'لینک اتاق آنلاین (اختیاری)'];
  echo '<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">';
  foreach($fields as $key=>$label){$v=get_post_meta($post->ID,'_eao_'.$key,true); echo '<label>'.esc_html($label).'<input style="display:block;width:100%;margin-top:5px" name="eao_'.$key.'" value="'.esc_attr($v).'"></label>';}
  echo '</div><p>این سامانه فقط دوره آنلاین می‌پذیرد. لینک اتاق جلسه فقط برای کاربران ثبت‌نام‌شده نمایش داده می‌شود.</p>';
 }
 public function save_course($post_id,$post){
  if(!isset($_POST['eao_course_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eao_course_nonce'])),'eao_save_course')||defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE||!current_user_can('edit_post',$post_id))return;
  foreach(['level','price','capacity','start','end','schedule','teacher','meeting'] as $k){if(isset($_POST['eao_'.$k])) update_post_meta($post_id,'_eao_'.$k,sanitize_text_field(wp_unslash($_POST['eao_'.$k])));}
 }
 public function assets(){
  wp_register_style('eao-style',false,[],self::VERSION);wp_enqueue_style('eao-style');wp_add_inline_style('eao-style',$this->css());
 }
 private function css(){return '.eao{direction:rtl;font-family:inherit;color:#17233e;background:#f4f6fb;border-radius:20px;padding:22px}.eao *{box-sizing:border-box}.eao-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px}.eao h2{font-size:24px;margin:0}.eao-sub{color:#77839b;font-size:13px;margin-top:6px}.eao-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:15px}.eao-card{background:#fff;border:1px solid #e6eaf2;border-radius:15px;padding:17px;min-width:0}.eao-card h3{font-size:17px;margin:0 0 10px}.eao-meta{display:grid;gap:7px;color:#64718a;font-size:12px;margin:12px 0}.eao-btn{display:inline-block;border:0;border-radius:10px;background:#435cf5;color:#fff!important;text-decoration:none;padding:10px 15px;cursor:pointer;font:inherit}.eao-btn.alt{background:#e9edff;color:#344bd5!important}.eao-input{width:100%;border:1px solid #dce2ed;border-radius:9px;padding:11px;background:white}.eao-field{display:grid;gap:6px;margin:12px 0;font-size:13px}.eao-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin:15px 0}.eao-stat{background:#fff;border:1px solid #e6eaf2;border-radius:13px;padding:15px}.eao-stat strong{display:block;font-size:23px;margin-top:6px}.eao-alert{padding:12px;background:#fff4dc;border-radius:10px;color:#77520a;font-size:13px;margin:12px 0}.eao-success{padding:12px;background:#e6f8f0;border-radius:10px;color:#176b4c;margin:12px 0}.eao-table{width:100%;border-collapse:collapse;background:white}.eao-table td,.eao-table th{padding:11px;border-bottom:1px solid #edf0f5;text-align:right;font-size:13px}@media(max-width:600px){.eao{padding:14px}.eao h2{font-size:20px}}';}
 public function handle_forms(){
  if(!isset($_POST['eao_action']))return;
  if(!is_user_logged_in())return;
  if($_POST['eao_action']==='enroll'){
   if(!isset($_POST['_wpnonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])),'eao_enroll'))return;
   $course=absint($_POST['course_id']??0);$p=get_post($course);
   if(!$p||$p->post_type!=='eao_course'||$p->post_status!=='publish')return;
   $capacity=(int)get_post_meta($course,'_eao_capacity',true);
   $count=$this->enrollment_count($course);
   if($capacity>0&&$count>=$capacity){$this->notice('ظرفیت این دوره تکمیل شده است.','error');return;}
   $existing=get_posts(['post_type'=>'eao_booking','author'=>get_current_user_id(),'meta_key'=>'_eao_course','meta_value'=>$course,'numberposts'=>1]);
   if($existing){$this->notice('قبلاً برای این دوره ثبت‌نام کرده‌اید.','error');return;}
   $id=wp_insert_post(['post_type'=>'eao_booking','post_status'=>'publish','post_title'=>'ثبت‌نام: '.$p->post_title,'post_author'=>get_current_user_id()]);
   if($id&&!is_wp_error($id)){update_post_meta($id,'_eao_course',$course);update_post_meta($id,'_eao_booking_status','pending_payment');$this->notice('درخواست ثبت‌نام ثبت شد. پس از تأیید پرداخت، دسترسی دوره فعال می‌شود.','success');}
  }
 }
 private function notice($message,$type){set_transient('eao_notice_'.get_current_user_id(),['message'=>$message,'type'=>$type],60);}
 private function show_notice(){ $n=get_transient('eao_notice_'.get_current_user_id());if($n){delete_transient('eao_notice_'.get_current_user_id());echo '<div class="eao-'.($n['type']==='success'?'success':'alert').'">'.esc_html($n['message']).'</div>';}}
 private function enrollment_count($course){$q=new WP_Query(['post_type'=>'eao_booking','post_status'=>'publish','meta_key'=>'_eao_course','meta_value'=>$course,'fields'=>'ids','posts_per_page'=>-1]);return $q->found_posts;}
 public function catalog(){
  $q=new WP_Query(['post_type'=>'eao_course','post_status'=>'publish','posts_per_page'=>-1]);
  ob_start();?><div class="eao"><div class="eao-head"><div><h2>دوره‌های آنلاین Erfan Academy</h2><div class="eao-sub">یادگیری آنلاین، از هرجا و در هر زمان</div></div><form method="get"><input class="eao-input" style="max-width:220px" name="eao_search" placeholder="جست‌وجوی دوره..." value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_GET['eao_search']??''))); ?>"></form></div><div class="eao-grid"><?php
  $search=sanitize_text_field(wp_unslash($_GET['eao_search']??''));
  if($search)$q->posts=array_values(array_filter($q->posts,function($p)use($search){return stripos($p->post_title,$search)!==false||stripos($p->post_content,$search)!==false;}));
  if($q->posts)foreach($q->posts as $p){$id=$p->ID;$cap=(int)get_post_meta($id,'_eao_capacity',true);$count=$this->enrollment_count($id);?><article class="eao-card"><h3><?php echo esc_html($p->post_title);?></h3><div><?php echo esc_html(wp_trim_words($p->post_content,25));?></div><div class="eao-meta"><span>سطح: <?php echo esc_html(get_post_meta($id,'_eao_level',true)?:'همه سطوح');?></span><span>مدرس: <?php echo esc_html(get_post_meta($id,'_eao_teacher',true)?:'تعیین نشده');?></span><span>زمان: <?php echo esc_html(get_post_meta($id,'_eao_schedule',true)?:'به‌زودی');?></span><span>شهریه: <?php echo esc_html(get_post_meta($id,'_eao_price',true)?:'رایگان');?> تومان</span><span>ظرفیت باقی‌مانده: <?php echo $cap?max(0,$cap-$count):'نامحدود';?></span></div><?php if(is_user_logged_in()):?><form method="post"><?php wp_nonce_field('eao_enroll');?><input type="hidden" name="eao_action" value="enroll"><input type="hidden" name="course_id" value="<?php echo $id;?>"><button class="eao-btn" type="submit">درخواست ثبت‌نام</button></form><?php else:?><a class="eao-btn" href="<?php echo esc_url(wp_login_url(get_permalink()));?>">ورود برای ثبت‌نام</a><?php endif;?></article><?php }else echo '<div class="eao-card">هنوز دوره‌ای منتشر نشده است.</div>';?></div></div><?php return ob_get_clean();
 }
 public function my_courses(){
  if(!is_user_logged_in())return '<div class="eao">برای مشاهده دوره‌ها وارد حساب شوید.</div>';
  $q=new WP_Query(['post_type'=>'eao_booking','post_status'=>'publish','author'=>get_current_user_id(),'posts_per_page'=>-1]);
  ob_start();?><div class="eao"><h2>دوره‌های من</h2><?php if($q->have_posts()):?><div class="eao-grid"><?php while($q->have_posts()):$q->the_post();$course=absint(get_post_meta(get_the_ID(),'_eao_course',true));$status=get_post_meta(get_the_ID(),'_eao_booking_status',true);?><div class="eao-card"><h3><?php echo esc_html(get_the_title($course));?></h3><p>وضعیت: <?php echo esc_html($status==='active'?'فعال':'در انتظار پرداخت/تأیید');?></p><?php if($status==='active'){$link=get_post_meta($course,'_eao_meeting',true);if($link)echo '<a class="eao-btn" rel="noopener" target="_blank" href="'.esc_url($link).'">ورود به کلاس آنلاین</a>';}?></div><?php endwhile;wp_reset_postdata();?></div><?php else:?><p>هنوز برای دوره‌ای ثبت‌نام نکرده‌اید.</p><?php endif;?></div><?php return ob_get_clean();
 }
 public function portal(){
  if(!is_user_logged_in())return '<div class="eao"><h2>ورود به آکادمی</h2><p>برای ورود و ثبت‌نام از سامانه کاربران وردپرس استفاده کنید.</p><a class="eao-btn" href="'.esc_url(wp_login_url(get_permalink())).'">ورود</a> <a class="eao-btn alt" href="'.esc_url(wp_registration_url()).'">ثبت‌نام</a></div>';
  $u=wp_get_current_user();$roles=(array)$u->roles;$role=in_array('administrator',$roles,true)?'مدیر':(in_array('eao_teacher',$roles,true)?'مدرس':(in_array('eao_support',$roles,true)?'پشتیبانی':'دانش‌آموز'));
  $menus=['دانش‌آموز'=>['دوره‌های من','کلاس‌های آنلاین','آزمون‌ها و تکالیف','پیشرفت و گواهی‌ها','پشتیبانی'],'مدرس'=>['دوره‌های من','هنرجویان','کلاس‌های زنده','حضور و غیاب','آزمون و تکلیف'],'مدیر'=>['مدیریت کاربران','مدیریت مدرس‌ها','دوره‌های آنلاین','ثبت‌نام‌ها','گزارش‌ها','تنظیمات اتصال'],'پشتیبانی'=>['تیکت‌های جدید','در انتظار پاسخ','گفت‌وگوها','مشکلات پرداخت']];
  ob_start();?><div class="eao"><div class="eao-head"><div><h2>پنل <?php echo esc_html($role);?></h2><div class="eao-sub">خوش آمدی، <?php echo esc_html($u->display_name);?></div></div><a class="eao-btn alt" href="<?php echo esc_url(wp_logout_url(get_permalink()));?>">خروج</a></div><div class="eao-stats"><div class="eao-stat">نقش کاربری<strong><?php echo esc_html($role);?></strong></div><div class="eao-stat">دوره‌های ثبت‌نام‌شده<strong><?php echo count(get_posts(['post_type'=>'eao_booking','author'=>$u->ID,'numberposts'=>-1]));?></strong></div><div class="eao-stat">نوع آموزش<strong>فقط آنلاین</strong></div></div><div class="eao-grid"><?php foreach($menus[$role] as $m):?><div class="eao-card"><h3><?php echo esc_html($m);?></h3><p class="eao-sub">بخش اختصاصی <?php echo esc_html($role);?></p><?php if($m==='دوره‌های من'):?><a class="eao-btn" href="<?php echo esc_url(add_query_arg('view','my-courses',get_permalink()));?>">مشاهده</a><?php elseif($m==='دوره‌های آنلاین'):?><a class="eao-btn" href="<?php echo esc_url(admin_url('edit.php?post_type=eao_course'));?>">مدیریت</a><?php else:?><span class="eao-sub">زیرساخت این بخش در حال توسعه اتصال سرویس است.</span><?php endif;?></div><?php endforeach;?></div><?php if(isset($_GET['view'])&&$_GET['view']==='my-courses')echo $this->my_courses();?></div><?php return ob_get_clean();
 }
 public function admin_menu(){add_menu_page('Erfan Academy','Erfan Academy','manage_options','eao-home',[$this,'admin_page'],'dashicons-welcome-learn-more',58);}
 public function settings(){register_setting('eao_settings','eao_zarinpal_merchant');register_setting('eao_settings','eao_bbb_endpoint');register_setting('eao_settings','eao_bbb_secret');}
 public function admin_page(){if(!current_user_can('manage_options'))return;?><div class="wrap"><h1>Erfan Academy Online</h1><p>سامانه فقط آنلاین است. دوره‌ها را از منوی «دوره‌های آنلاین» ایجاد کنید.</p><h2>اتصال سرویس‌ها</h2><form method="post" action="options.php"><?php settings_fields('eao_settings');?><table class="form-table"><tr><th>زرین‌پال Merchant ID</th><td><input class="regular-text" name="eao_zarinpal_merchant" value="<?php echo esc_attr(get_option('eao_zarinpal_merchant'));?>"></td></tr><tr><th>نشانی API سرور BigBlueButton</th><td><input class="regular-text" name="eao_bbb_endpoint" value="<?php echo esc_attr(get_option('eao_bbb_endpoint'));?>"></td></tr><tr><th>Secret سرور BigBlueButton</th><td><input type="password" autocomplete="new-password" class="regular-text" name="eao_bbb_secret" value="<?php echo esc_attr(get_option('eao_bbb_secret'));?>"></td></tr></table><?php submit_button('ذخیره تنظیمات');?></form><div class="notice notice-warning"><p>ذخیره این مقادیر به‌تنهایی به معنی اتصال درگاه یا ساخت اتاق BBB نیست. اتصال API و آزمون تراکنش/جلسه باید در مرحله بعد تکمیل شود. بانک ملت نیز نیازمند قرارداد و اطلاعات پذیرندگی است.</p></div><h2>شورت‌کدها</h2><p><code>[erfan_academy_courses]</code> فهرست دوره‌ها</p><p><code>[erfan_academy_portal]</code> پنل کاربری</p><p><code>[erfan_academy_my_courses]</code> دوره‌های من</p></div><?php }
}
register_activation_hook(__FILE__,function(){ $p=new Erfan_Academy_Online();$p->roles();$p->types();flush_rewrite_rules();});
register_deactivation_hook(__FILE__,function(){flush_rewrite_rules();});
new Erfan_Academy_Online();
