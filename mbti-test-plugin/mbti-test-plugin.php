<?php
/**
 * Plugin Name: MBTI Personality Test
 * Plugin URI: https://mbti.friday-go.icu/mbti-test-plugin
 * Description: A comprehensive MBTI personality test plugin that helps users understand their personality type.
 * Version: 1.0.0
 * Author: PFinalClub
 * Author URI: https://member.friday-go.icu
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: mbti-test-plugin
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
 * 加载插件文本域
 * 必须在 init 钩子中调用，符合 WordPress 官方标准
 */
function mbti_load_textdomain() {
    load_plugin_textdomain( 
        'mbti-test-plugin', 
        false, 
        dirname( plugin_basename( __FILE__ ) ) . '/languages' 
    );
}
add_action( 'init', 'mbti_load_textdomain' );

/**
 * 插件初始化
 */
function mbti_plugin_init() {
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
function mbti_translate( $text, $domain = 'mbti-test-plugin' ) {
    return esc_html__( $text, $domain );
}

/**
 * 输出翻译后的文本
 */
function mbti_e( $text, $domain = 'mbti-test-plugin' ) {
    echo esc_html__( $text, $domain );
}

/**
 * 获取本地化的题目数据
 */
function mbti_get_localized_questions() {
    // 获取当前语言环境
    $current_locale = get_locale();
    
    // 根据语言环境选择题库文件
    if ( strpos( $current_locale, 'zh' ) === 0 ) {
        // 中文环境使用中文题库
        $questions_file = MBTI_PLUGIN_DIR . 'data/basic_questions.json';
    } else {
        // 其他语言环境使用英文题库
        $questions_file = MBTI_PLUGIN_DIR . 'data/basic_questions_en.json';
        
        // 如果英文题库不存在，回退到中文题库并进行翻译
        if ( ! file_exists( $questions_file ) ) {
            $questions_file = MBTI_PLUGIN_DIR . 'data/basic_questions.json';
        }
    }
    
    if ( ! file_exists( $questions_file ) ) {
        return false;
    }
    
    $questions_data = file_get_contents( $questions_file );
    $questions = json_decode( $questions_data, true );
    
    if ( ! $questions || ! isset( $questions['questions'] ) ) {
        return false;
    }
    
    // 如果使用的是中文题库但当前环境不是中文，需要进行翻译
    if ( basename( $questions_file ) === 'basic_questions.json' && strpos( $current_locale, 'zh' ) !== 0 ) {
        // 对题目文本进行翻译
        foreach ( $questions['questions'] as &$question ) {
            // 翻译题目文本
            $question['text'] = esc_html__( $question['text'], 'mbti-test-plugin' );
            
            // 翻译选项文本
            foreach ( $question['options'] as &$option ) {
                $option['text'] = esc_html__( $option['text'], 'mbti-test-plugin' );
            }
            unset( $option );
        }
        unset( $question );
        
        // 对性格类型进行翻译
        if ( isset( $questions['results'] ) ) {
            foreach ( $questions['results'] as &$type ) {
                $type['name'] = esc_html__( $type['name'], 'mbti-test-plugin' );
                $type['description'] = esc_html__( $type['description'], 'mbti-test-plugin' );
            }
            unset( $type );
        }
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