<?php
/**
 * MBTI测试插件管理部分
 * 处理后台管理功能
 */

/**
 * 添加管理菜单
 */
function mbti_add_admin_menu() {
    // 添加主菜单
    add_menu_page(
        __( 'MBTI Test Plugin', 'mbti-test' ),
        __( 'MBTI Test', 'mbti-test' ),
        'manage_options',
        'mbti-test-plugin',
        'mbti_admin_dashboard',
        'dashicons-welcome-write-blog',
        60
    );
    
    // 添加子菜单
    add_submenu_page(
        'mbti-test-plugin',
        __( 'Plugin Settings', 'mbti-test' ),
        __( 'Settings', 'mbti-test' ),
        'manage_options',
        'mbti-settings',
        'mbti_settings_page'
    );
    
    add_submenu_page(
        'mbti-test-plugin',
        __( 'Question Bank Management', 'mbti-test' ),
        __( 'Question Management', 'mbti-test' ),
        'manage_options',
        'mbti-questions',
        'mbti_questions_page'
    );
}

// 添加管理菜单钩子
add_action( 'admin_menu', 'mbti_add_admin_menu' );

/**
 * 管理员仪表盘
 */
function mbti_admin_dashboard() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'MBTI Test Plugin Dashboard', 'mbti-test' ); ?></h1>
        <div class="mbti-dashboard-stats">
            <div class="mbti-stat-card">
                <div class="mbti-stat-title"><?php _e( 'Total Questions', 'mbti-test' ); ?></div>
                <div class="mbti-stat-value"><?php echo mbti_get_total_questions(); ?></div>
            </div>
            <div class="mbti-stat-card">
                <div class="mbti-stat-title"><?php _e( 'Total Tests', 'mbti-test' ); ?></div>
                <div class="mbti-stat-value"><?php echo mbti_get_total_tests(); ?></div>
            </div>
        </div>
        
        <div class="mbti-dashboard-content">
            <div class="postbox">
                <h3 class="hndle"><span><?php _e( 'Plugin Introduction', 'mbti-test' ); ?></span></h3>
                <div class="inside">
                    <p><?php printf( __( 'MBTI test plugin is a fully functional personality test tool. You can embed tests in any page or article through the shortcode %s.', 'mbti-test' ), '<code>[mbti_test]</code>' ); ?></p>
                    <p><?php _e( 'Main Features:', 'mbti-test' ); ?></p>
                    <ul>
                        <li><?php _e( 'Online MBTI test question bank', 'mbti-test' ); ?></li>
                        <li><?php _e( 'Test result generation and display', 'mbti-test' ); ?></li>
                        <li><?php _e( 'Support shortcode embedding', 'mbti-test' ); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="postbox">
                <h3 class="hndle"><span><?php _e( 'Usage Guide', 'mbti-test' ); ?></span></h3>
                <div class="inside">
                    <p><?php printf( __( '1. Add or edit test questions in the %s page', 'mbti-test' ), '<code>' . __( 'Question Management', 'mbti-test' ) . '</code>' ); ?></p>
                    <p><?php printf( __( '2. Configure plugin options in the %s page', 'mbti-test' ), '<code>' . __( 'Settings', 'mbti-test' ) . '</code>' ); ?></p>
                    <p><?php printf( __( '3. Use shortcode %s in pages or articles to embed tests', 'mbti-test' ), '<code>[mbti_test]</code>' ); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .mbti-dashboard-stats {
        display: flex;
        margin-bottom: 20px;
        gap: 20px;
    }
    
    .mbti-stat-card {
        flex: 1;
        background: white;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .mbti-stat-title {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }
    
    .mbti-stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #4caf50;
    }
    
    .mbti-dashboard-content {
        margin-top: 20px;
    }
    </style>
    <?php
}

/**
 * 插件设置页面
 */
function mbti_settings_page() {
    // 保存设置
    if ( isset( $_POST['mbti_save_settings'] ) && check_admin_referer( 'mbti_save_settings', 'mbti_settings_nonce' ) ) {
        $settings = array(
            'show_ads' => isset( $_POST['mbti_show_ads'] ) ? 1 : 0,
            'question_order' => sanitize_text_field( $_POST['mbti_question_order'] ),
        );
        
        update_option( 'mbti_plugin_settings', $settings );
        
        echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Settings saved', 'mbti-test' ) . '</p></div>';
    }
    
    // 获取当前设置
    $settings = get_option( 'mbti_plugin_settings', array(
        'show_ads' => true,
        'question_order' => 'random',
    ) );
    
    ?>
    <div class="wrap">
        <h1><?php _e( 'Plugin Settings', 'mbti-test' ); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'mbti_save_settings', 'mbti_settings_nonce' ); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Show Ads', 'mbti-test' ); ?></th>
                    <td>
                        <input type="checkbox" name="mbti_show_ads" id="mbti_show_ads" value="1" <?php checked( $settings['show_ads'], 1 ); ?>>
                        <label for="mbti_show_ads"><?php _e( 'Display ads on test results page', 'mbti-test' ); ?></label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e( 'Question Order', 'mbti-test' ); ?></th>
                    <td>
                        <select name="mbti_question_order" id="mbti_question_order">
                            <option value="random" <?php selected( $settings['question_order'], 'random' ); ?>><?php _e( 'Random', 'mbti-test' ); ?></option>
                            <option value="fixed" <?php selected( $settings['question_order'], 'fixed' ); ?>><?php _e( 'Fixed', 'mbti-test' ); ?></option>
                        </select>
                    </td>
                </tr>
                

            </table>
            
            <p class="submit">
                <input type="submit" name="mbti_save_settings" id="mbti_save_settings" class="button button-primary" value="<?php _e( 'Save Settings', 'mbti-test' ); ?>">
            </p>
        </form>
    </div>
    <?php
}

/**
 * 题库管理页面
 */
function mbti_questions_page() {
    // 处理题目操作
    if ( isset( $_POST['mbti_save_question'] ) && check_admin_referer( 'mbti_save_question', 'mbti_question_nonce' ) ) {
        mbti_save_question();
    }
    
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['question_id'] ) ) {
        mbti_delete_question( $_GET['question_id'] );
    }
    
    // 显示题目列表或编辑表单
    if ( isset( $_GET['action'] ) && ( $_GET['action'] === 'edit' || $_GET['action'] === 'add' ) ) {
        mbti_edit_question_form( isset( $_GET['question_id'] ) ? $_GET['question_id'] : 0 );
    } else {
        mbti_list_questions();
    }
}

/**
 * 获取总题目数
 */
function mbti_get_total_questions() {
    $questions_file = MBTI_PLUGIN_DIR . 'data/basic_questions.json';
    if ( file_exists( $questions_file ) ) {
        $questions_data = file_get_contents( $questions_file );
        $questions = json_decode( $questions_data, true );
        if ( $questions && isset( $questions['questions'] ) ) {
            return count( $questions['questions'] );
        }
    }
    return 0;
}

/**
 * 获取总测试次数
 */
function mbti_get_total_tests() {
    // 在实际项目中，这里应该从数据库中查询测试次数
    return 0;
}

/**
 * 保存题目
 */
function mbti_save_question() {
    // 在实际项目中，这里应该有完整的题目保存逻辑
    // 由于当前使用JSON文件存储，这个功能会比较复杂
    // 这里只是简单的演示
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Question saved', 'mbti-test' ) . '</p></div>';
}

/**
 * 删除题目
 */
function mbti_delete_question( $question_id ) {
    // 在实际项目中，这里应该有完整的题目删除逻辑
    // 由于当前使用JSON文件存储，这个功能会比较复杂
    // 这里只是简单的演示
    
    wp_redirect( admin_url( 'admin.php?page=mbti-questions' ) );
    exit;
}

/**
 * 题目编辑表单
 */
function mbti_edit_question_form( $question_id ) {
    // 在实际项目中，这里应该有完整的题目编辑表单
    // 由于当前使用JSON文件存储，这个功能会比较复杂
    // 这里只是简单的演示
    
    ?>
    <div class="wrap">
        <h1><?php echo $question_id > 0 ? __( 'Edit Question', 'mbti-test' ) : __( 'Add Question', 'mbti-test' ); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'mbti_save_question', 'mbti_question_nonce' ); ?>
            <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Question Text', 'mbti-test' ); ?></th>
                    <td>
                        <textarea name="question_text" rows="4" class="regular-text" required></textarea>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e( 'Question Type', 'mbti-test' ); ?></th>
                    <td>
                        <select name="question_type" required>
                            <option value="EI"><?php _e( 'Extraversion (E) / Introversion (I)', 'mbti-test' ); ?></option>
                            <option value="SN"><?php _e( 'Sensing (S) / Intuition (N)', 'mbti-test' ); ?></option>
                            <option value="TF"><?php _e( 'Thinking (T) / Feeling (F)', 'mbti-test' ); ?></option>
                            <option value="JP"><?php _e( 'Judging (J) / Perceiving (P)', 'mbti-test' ); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e( 'Option 1', 'mbti-test' ); ?></th>
                    <td>
                        <input type="text" name="option1_text" class="regular-text" required>
                        <input type="hidden" name="option1_score" value="0">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e( 'Option 2', 'mbti-test' ); ?></th>
                    <td>
                        <input type="text" name="option2_text" class="regular-text" required>
                        <input type="hidden" name="option2_score" value="1">
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="mbti_save_question" class="button button-primary" value="<?php _e( 'Save Question', 'mbti-test' ); ?>">
                <a href="<?php echo admin_url( 'admin.php?page=mbti-questions' ); ?>" class="button"><?php _e( 'Cancel', 'mbti-test' ); ?></a>
            </p>
        </form>
    </div>
    <?php
}

/**
 * 题目列表
 */
function mbti_list_questions() {
    // 读取基础题库
    $questions_file = MBTI_PLUGIN_DIR . 'data/basic_questions.json';
    $questions = array();
    
    if ( file_exists( $questions_file ) ) {
        $questions_data = file_get_contents( $questions_file );
        $questions = json_decode( $questions_data, true );
    }
    
    ?>
    <div class="wrap">
        <h1><?php _e( 'Question Bank Management', 'mbti-test' ); ?></h1>
        <a href="<?php echo admin_url( 'admin.php?page=mbti-questions&action=add' ); ?>" class="button button-primary" style="margin-bottom: 15px;"><?php _e( 'Add Question', 'mbti-test' ); ?></a>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php _e( 'Question Text', 'mbti-test' ); ?></th>
                    <th><?php _e( 'Type', 'mbti-test' ); ?></th>
                    <th><?php _e( 'Actions', 'mbti-test' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $questions && isset( $questions['questions'] ) && ! empty( $questions['questions'] ) ) : ?>
                    <?php foreach ( $questions['questions'] as $question ) : ?>
                    <tr>
                        <td><?php echo $question['id']; ?></td>
                        <td><?php echo $question['text']; ?></td>
                        <td><?php echo $question['type']; ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=mbti-questions&action=edit&question_id=' . $question['id'] ); ?>"><?php _e( 'Edit', 'mbti-test' ); ?></a> | 
                            <a href="<?php echo admin_url( 'admin.php?page=mbti-questions&action=delete&question_id=' . $question['id'] ); ?>" onclick="return confirm('<?php _e( 'Are you sure you want to delete this question?', 'mbti-test' ); ?>');"><?php _e( 'Delete', 'mbti-test' ); ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" style="text-align: center;"><?php _e( 'No questions', 'mbti-test' ); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * 加载管理样式
 */
function mbti_load_admin_styles() {
    wp_enqueue_style( 'mbti-admin-styles', MBTI_PLUGIN_URL . 'admin/css/mbti-admin.css' );
}

// 添加管理样式钩子
add_action( 'admin_enqueue_scripts', 'mbti_load_admin_styles' );