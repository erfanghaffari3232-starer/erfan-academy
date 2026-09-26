<?php
/**
 * Plugin Name: Erfan Online Class
 * Description: سیستم کلاس آنلاین عرفان برای وردپرس - نسخه 1
 * Version: 1.0.0
 * Author: Erfan
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH')) exit;
class Erfan_Online_Class {
 private $key='eoc_classes';
 function __construct(){add_shortcode('erfan_online_class',[$this,'shortcode']);add_action('wp_enqueue_scripts',[$this,'assets']);}
 function assets(){wp_enqueue_style('eoc-style',plugins_url('assets/style.css',__FILE__),[], '1.0.0');}
 function classes(){return get_option($this->key,[]);}
 function save($v){update_option($this->key,$v,false);}
 function shortcode(){
  $classes=$this->classes();$notice='';
  if(isset($_POST['eoc_action'])&&check_admin_referer('eoc_action','eoc_nonce')){
   $a=sanitize_text_field(wp_unslash($_POST['eoc_action']));
   if($a==='create'){ $n=sanitize_text_field(wp_unslash($_POST['class_name']??'')); if($n){$id='ERF-'.wp_rand(100,999);$u=wp_get_current_user();$classes[$id]=['name'=>$n,'teacher'=>$u->display_name?:'مدرس','lessons'=>[]];$this->save($classes);$notice='کلاس ساخته شد. کد ورود: '.$id;}}
   if($a==='lesson'){ $id=sanitize_text_field(wp_unslash($_POST['class_id']??''));$l=sanitize_text_field(wp_unslash($_POST['lesson_name']??''));if($id&&$l&&isset($classes[$id])){$classes[$id]['lessons'][]=$l;$this->save($classes);$notice='درس اضافه شد.';}}
  }
  ob_start();?>
  <div class="eoc" dir="rtl">
   <div class="eoc-brand">🎓 Erfan <span>Online Class</span></div>
   <p class="eoc-muted">سیستم کلاس آنلاین عرفان — نسخه ۱</p>
   <?php if($notice):?><div class="eoc-notice"><?php echo esc_html($notice);?></div><?php endif;?>
   <div class="eoc-grid">
    <div class="eoc-card"><h3>🏫 ساخت کلاس</h3><form method="post"><?php wp_nonce_field('eoc_action','eoc_nonce');?><input type="hidden" name="eoc_action" value="create"><input name="class_name" required placeholder="نام کلاس"><button>ساخت کلاس</button></form></div>
    <div class="eoc-card"><h3>📚 کلاس‌ها</h3>
    <?php if(!$classes):?><p class="eoc-muted">هنوز کلاسی ساخته نشده.</p><?php else:foreach($classes as $id=>$c):?>
     <div class="eoc-class"><strong><?php echo esc_html($c['name']);?></strong><div class="eoc-muted">مدرس: <?php echo esc_html($c['teacher']);?></div><div>کد: <b class="eoc-code"><?php echo esc_html($id);?></b></div>
     <?php if(!empty($c['lessons'])):?><ul><?php foreach($c['lessons'] as $l):?><li>📘 <?php echo esc_html($l);?></li><?php endforeach;?></ul><?php endif;?>
     <form method="post"><?php wp_nonce_field('eoc_action','eoc_nonce');?><input type="hidden" name="eoc_action" value="lesson"><input type="hidden" name="class_id" value="<?php echo esc_attr($id);?>"><input name="lesson_name" required placeholder="عنوان درس"><button>+ افزودن درس</button></form></div>
    <?php endforeach;endif;?></div>
   </div>
   <div class="eoc-live"><h3>🎥 کلاس زنده</h3><p class="eoc-muted">اتصال ویدئویی در مرحله بعد اضافه می‌شود.</p></div>
  </div><?php return ob_get_clean();
 }
}
new Erfan_Online_Class();
