<?php
/**
 * Plugin Name: MBTI Personality Test
 * Plugin URI: https://mbti.friday-go.icu/mbti-test-plugin
 * Description: A comprehensive MBTI personality test plugin that helps users understand their personality type.
 * Version: 1.1.0
 * Author: PFinalClub
 * Author URI: https://member.friday-go.icu
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: mbti-test
 * Domain Path: /languages
 */

// 防止直接访问文件
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 定义插件常量
define( 'MBTI_PLUGIN_VERSION', '1.1.0' );
define( 'MBTI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MBTI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * 插件初始化
 */
function mbti_plugin_init() {
    // 加载插件文本域，自动跟随WordPress语言设置
   // 加载语言文件
// 先尝试从WordPress全局语言目录加载
$current_locale = get_locale();
$global_language_file = WP_CONTENT_DIR . '/languages/plugins/mbti-test-' . $current_locale . '.mo';

if ( file_exists( $global_language_file ) ) {
    // 先移除可能已加载的文本域
    unload_textdomain( 'mbti-test' );
    // 加载当前语言的文件
    load_textdomain( 'mbti-test', $global_language_file );
} else {
    // 如果没有当前语言的文件，尝试加载英文作为默认
    $en_language_file = WP_CONTENT_DIR . '/languages/plugins/mbti-test-en_US.mo';
    if ( file_exists( $en_language_file ) ) {
        unload_textdomain( 'mbti-test' );
        load_textdomain( 'mbti-test', $en_language_file );
    }
}

// 最后尝试从插件目录加载作为后备
load_plugin_textdomain( 'mbti-test', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
    
/**
 * 注册短代码和管理功能
 */
function mbti_register_shortcode_and_admin() {
    // 注册短代码
    add_shortcode( 'mbti_test', 'mbti_test_shortcode_handler' );
    
    // 如果是管理员，则加载管理功能
    if ( is_admin() ) {
        require_once MBTI_PLUGIN_DIR . 'admin/mbti-admin.php';
    }
}


// 在插件加载时初始化
add_action( 'plugins_loaded', 'mbti_plugin_init' );

// 在WordPress初始化时注册短代码和管理功能
add_action( 'init', 'mbti_register_shortcode_and_admin' );

/**
 * 获取当前语言设置
 */
function mbti_get_current_language() {
    return get_locale();
}

/**
 * 获取翻译后的文本
 */
function mbti_translate( $text, $domain = 'mbti-test' ) {
    return __( $text, $domain );
}

/**
 * 输出翻译后的文本
 */
function mbti_e( $text, $domain = 'mbti-test' ) {
    _e( $text, $domain );
}

/**
 * 获取本地化的题目数据
 */
function mbti_get_localized_questions() {
    // 读取基础题库
    $questions_file = MBTI_PLUGIN_DIR . 'data/basic_questions.json';
    if ( ! file_exists( $questions_file ) ) {
        return false;
    }
    
    $questions_data = file_get_contents( $questions_file );
    $questions = json_decode( $questions_data, true );
    
    if ( ! $questions || ! isset( $questions['questions'] ) ) {
        return false;
    }
    
    // 对题目文本进行翻译
    foreach ( $questions['questions'] as &$question ) {
        // 翻译题目文本
        $question['text'] = mbti_translate( $question['text'] );
        
        // 翻译选项文本
        foreach ( $question['options'] as &$option ) {
            $option['text'] = mbti_translate( $option['text'] );
        }
        unset( $option );
    }
    unset( $question );
    
    // 对性格类型进行翻译
    if ( isset( $questions['types'] ) ) {
        foreach ( $questions['types'] as &$type ) {
            $type['name'] = mbti_translate( $type['name'] );
            $type['description'] = mbti_translate( $type['description'] );
        }
        unset( $type );
    }
    
    return $questions;
}

/**
 * 兼容旧版本的英文翻译函数（保留但不再使用）
 */
function mbti_translate_questions_to_english( $questions ) {
    return $questions;
}

/**
 * 短代码处理函数
 */
function mbti_test_shortcode_handler( $atts ) {
    // 默认属性
    $atts = shortcode_atts( array(
        'type' => 'basic', // basic
        'show_progress' => true,
        'show_share' => true,
    ), $atts );
    
    // 包含前端显示功能
    require_once MBTI_PLUGIN_DIR . 'public/mbti-public.php';
    
    // 渲染基础版测试
    return mbti_render_basic_test( $atts );
}

/**
 * 插件激活钩子
 */
function mbti_plugin_activate() {
    // 在插件激活时执行的操作
    // 创建必要的数据库表或选项
    add_option( 'mbti_plugin_settings', array(
        'show_ads' => true,
        'question_order' => 'random',
    ) );
    
    // 复制语言文件到WordPress语言目录
    mbti_copy_language_files();
}

/**
 * 复制语言文件到WordPress语言目录
 */
function mbti_copy_language_files() {
    $source_dir = MBTI_PLUGIN_DIR . 'languages/';
    $target_dir = WP_CONTENT_DIR . '/languages/plugins/';
    
    // 确保目标目录存在
    if ( ! file_exists( $target_dir ) ) {
        wp_mkdir_p( $target_dir );
    }
    
    // 复制MO和PO文件
    $language_files = array(
        'mbti-test-zh_CN.mo',
        'mbti-test-zh_CN.po',
        'mbti-test-en_US.mo',
        'mbti-test-en_US.po'
    );
    
    foreach ( $language_files as $file ) {
        $source_file = $source_dir . $file;
        $target_file = $target_dir . $file;
        
        if ( file_exists( $source_file ) && ! file_exists( $target_file ) ) {
            copy( $source_file, $target_file );
        }
    }
}

/**
 * 插件停用钩子
 */
function mbti_plugin_deactivate() {
    // 在插件停用时执行的操作
    // 不删除任何数据，以便用户重新激活时保持设置
}

/**
 * 插件卸载钩子
 */
function mbti_plugin_uninstall() {
    // 在插件卸载时执行的操作
    // 删除所有插件相关的选项
    delete_option( 'mbti_plugin_settings' );
}

// 注册激活、停用和卸载钩子
register_activation_hook( __FILE__, 'mbti_plugin_activate' );
register_deactivation_hook( __FILE__, 'mbti_plugin_deactivate' );
register_uninstall_hook( __FILE__, 'mbti_plugin_uninstall' );