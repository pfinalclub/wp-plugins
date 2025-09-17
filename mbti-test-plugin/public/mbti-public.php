<?php
/**
 * MBTI测试插件公共部分
 * 处理前端显示逻辑
 */

/**
 * 渲染基础版MBTI测试
 */
function mbti_render_basic_test( $atts ) {
    // 加载前端样式和脚本
    wp_enqueue_style( 'mbti-frontend-styles', MBTI_PLUGIN_URL . 'assets/css/mbti-frontend.css', array(), MBTI_PLUGIN_VERSION );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'mbti-frontend-js', MBTI_PLUGIN_URL . 'assets/js/mbti-frontend.js', array( 'jquery' ), MBTI_PLUGIN_VERSION, true );
    
    // 本地化脚本
    wp_localize_script( 'mbti-frontend-js', 'mbtiTranslations', array(
        'incomplete_test' => esc_html__( 'Please answer all questions before submitting.', 'mbti-test-plugin' ),
        'submitting' => esc_html__( 'Submitting...', 'mbti-test-plugin' ),
        'submit_test' => esc_html__( 'Submit Test', 'mbti-test-plugin' ),
        'test_completed' => esc_html__( 'Test completed successfully!', 'mbti-test-plugin' )
    ) );
    
    // 获取国际化后的题目数据
    $questions = mbti_get_localized_questions();
    
    if ( ! $questions || ! isset( $questions['questions'] ) ) {
        return '<div class="mbti-error">' . esc_html__( 'Question bank data format error.', 'mbti-test-plugin' ) . '</div>';
    }
    
    // 根据设置决定题目顺序
    $settings = get_option( 'mbti_plugin_settings', array(
        'show_ads' => true,
        'question_order' => 'random',
    ) );
    $question_order = isset( $settings['question_order'] ) ? $settings['question_order'] : 'random';
    
    if ( $question_order === 'random' ) {
        shuffle( $questions['questions'] );
    }
    
    // 生成测试HTML
    ob_start();
    ?>
    <div class="mbti-test-wrapper">
        <div class="mbti-test-container">
            <!-- 测试头部 -->
            <div class="mbti-test-header">
                <h2 class="mbti-test-title">
                    <span class="mbti-icon">🧠</span>
                    <?php echo esc_html__( 'MBTI Personality Test', 'mbti-test-plugin' ); ?>
                </h2>
                <p class="mbti-test-subtitle"><?php echo esc_html__( 'Discover your unique personality type through 16 carefully designed questions', 'mbti-test-plugin' ); ?></p>
            </div>

            <!-- 进度条 -->
            <?php if ( $atts['show_progress'] ) : ?>
            <div class="mbti-progress-section">
                <div class="mbti-progress-info">
                    <span class="mbti-progress-label"><?php echo esc_html__( 'Progress', 'mbti-test-plugin' ); ?></span>
                    <span class="mbti-progress-text">0/<?php echo esc_html( count( $questions['questions'] ) ); ?></span>
                </div>
                <div class="mbti-progress-bar">
                    <div class="mbti-progress-fill" style="width: 0%"></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 测试表单 -->
            <form id="mbti-test-form" class="mbti-test-form">
                <div class="mbti-questions-container">
                    <?php foreach ( $questions['questions'] as $index => $question ) : ?>
                    <div class="mbti-question-card" data-question-id="<?php echo esc_attr( $question['id'] ); ?>" data-type="<?php echo esc_attr( $question['type'] ); ?>">
                        <div class="mbti-question-header">
                            <div class="mbti-question-number">
                                <span class="mbti-question-badge"><?php echo esc_html( $index + 1 ); ?></span>
                                <span class="mbti-question-total">/ <?php echo esc_html( count( $questions['questions'] ) ); ?></span>
                            </div>
                        </div>
                        
                        <div class="mbti-question-content">
                            <h3 class="mbti-question-text"><?php echo esc_html( $question['text'] ); ?></h3>
                            
                            <div class="mbti-question-options">
                                <?php foreach ( $question['options'] as $option_index => $option ) : ?>
                                <label class="mbti-option-card">
                                    <input type="radio" 
                                           name="question_<?php echo esc_attr( $question['id'] ); ?>" 
                                           value="<?php echo esc_attr( $option['score'] ); ?>"
                                           class="mbti-option-input">
                                    <div class="mbti-option-content">
                                        <div class="mbti-option-indicator"></div>
                                        <div class="mbti-option-text"><?php echo esc_html( $option['text'] ); ?></div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- 通知区域 -->
                <div id="mbti-notification" class="mbti-notification" style="display: none;">
                    <div class="mbti-notification-icon">⚠️</div>
                    <div class="mbti-notification-content"></div>
                    <button type="button" class="mbti-notification-close">×</button>
                </div>

                <!-- 导航按钮 -->
                <div class="mbti-navigation">
                    <button type="button" id="prev-question" class="mbti-nav-btn mbti-btn-secondary" disabled>
                        <span class="mbti-btn-icon">←</span>
                        <?php echo esc_html__( 'Previous', 'mbti-test-plugin' ); ?>
                    </button>
                    
                    <div class="mbti-nav-center">
                        <button type="submit" class="mbti-submit-btn mbti-btn-primary">
                            <span class="mbti-btn-icon">✓</span>
                            <?php echo esc_html__( 'Get My Results', 'mbti-test-plugin' ); ?>
                        </button>
                    </div>
                    
                    <button type="button" id="next-question" class="mbti-nav-btn mbti-btn-secondary">
                        <?php echo esc_html__( 'Next', 'mbti-test-plugin' ); ?>
                        <span class="mbti-btn-icon">→</span>
                    </button>
                </div>
            </form>

            <!-- 结果显示区域 -->
            <div id="mbti-results" class="mbti-results-section" style="display: none;">
                <div class="mbti-results-header">
                    <div class="mbti-results-icon">🎯</div>
                    <h3 class="mbti-results-title"><?php echo esc_html__( 'Your Personality Type', 'mbti-test-plugin' ); ?></h3>
                </div>
                
                <div class="mbti-result-card">
                    <div class="mbti-result-type"></div>
                    <div class="mbti-result-description"></div>
                </div>

                <?php if ( $atts['show_share'] ) : ?>
                <div class="mbti-share-section">
                    <h4 class="mbti-share-title"><?php echo esc_html__( 'Share Your Results', 'mbti-test-plugin' ); ?></h4>
                    <div class="mbti-share-buttons">
                        <button class="mbti-share-btn mbti-share-facebook" data-platform="facebook">
                            <span class="mbti-share-icon">📘</span>
                            <?php echo esc_html__( 'Facebook', 'mbti-test-plugin' ); ?>
                        </button>
                        <button class="mbti-share-btn mbti-share-twitter" data-platform="twitter">
                            <span class="mbti-share-icon">🐦</span>
                            <?php echo esc_html__( 'Twitter', 'mbti-test-plugin' ); ?>
                        </button>
                        <button class="mbti-share-btn mbti-share-wechat" data-platform="wechat">
                            <span class="mbti-share-icon">💬</span>
                            <?php echo esc_html__( 'WeChat', 'mbti-test-plugin' ); ?>
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 升级推广区域 -->
                <?php if ( $settings['show_ads'] ) : ?>
                <div class="mbti-upgrade-section">
                    <div class="mbti-upgrade-card">
                        <div class="mbti-upgrade-header">
                            <div class="mbti-upgrade-icon">⭐</div>
                            <h4 class="mbti-upgrade-title"><?php echo esc_html__( 'Unlock Premium Features', 'mbti-test-plugin' ); ?></h4>
                        </div>
                        
                        <p class="mbti-upgrade-description">
                            <?php echo esc_html__( 'Get deeper insights with our professional analysis and personalized recommendations', 'mbti-test-plugin' ); ?>
                        </p>
                        
                        <div class="mbti-upgrade-features">
                            <div class="mbti-feature-item">
                                <span class="mbti-feature-icon">📊</span>
                                <span class="mbti-feature-text"><?php echo esc_html__( 'Detailed personality analysis', 'mbti-test-plugin' ); ?></span>
                            </div>
                            <div class="mbti-feature-item">
                                <span class="mbti-feature-icon">💼</span>
                                <span class="mbti-feature-text"><?php echo esc_html__( 'Career recommendations', 'mbti-test-plugin' ); ?></span>
                            </div>
                            <div class="mbti-feature-item">
                                <span class="mbti-feature-icon">🤝</span>
                                <span class="mbti-feature-text"><?php echo esc_html__( 'Relationship compatibility', 'mbti-test-plugin' ); ?></span>
                            </div>
                            <div class="mbti-feature-item">
                                <span class="mbti-feature-icon">🚀</span>
                                <span class="mbti-feature-text"><?php echo esc_html__( 'Personal development tips', 'mbti-test-plugin' ); ?></span>
                            </div>
                        </div>
                        
                        <button class="mbti-upgrade-btn">
                            <span class="mbti-btn-icon">🔓</span>
                            <?php echo esc_html__( 'Upgrade Now', 'mbti-test-plugin' ); ?>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    (function($) {
        $(document).ready(function() {
            var totalQuestions = $('.mbti-question').length;
            var currentQuestion = 0;
            
            // 初始显示第一题
            $('.mbti-question').hide();
            $('.mbti-question').eq(0).show();
            
            // 更新导航按钮状态
            function updateNavButtons() {
                $('#prev-question').prop('disabled', currentQuestion === 0);
                $('#next-question').prop('disabled', currentQuestion === totalQuestions - 1);
            }
            
            // 实时更新进度
            function updateProgress() {
                var answered = 0;
                $('.mbti-question').each(function() {
                    var questionId = $(this).data('question-id');
                    if ($('input[name="question_' + questionId + '"]:checked').length > 0) {
                        answered++;
                    }
                });
                
                var progressPercent = (answered / totalQuestions) * 100;
                $('.mbti-progress-fill').css('width', progressPercent + '%');
                $('.mbti-progress-text').text(answered + '/' + totalQuestions);
            }
            
            // 切换到指定问题
            function goToQuestion(index) {
                $('.mbti-question').hide();
                $('.mbti-question').eq(index).show();
                currentQuestion = index;
                updateNavButtons();
            }
            
            // 监听选项变化，更新进度和自动切换
            $('input[type="radio"]').change(function() {
                updateProgress();
                
                // 如果不是最后一题，自动切换到下一题
                if (currentQuestion < totalQuestions - 1) {
                    setTimeout(function() {
                        goToQuestion(currentQuestion + 1);
                    }, 500); // 延迟500毫秒切换，让用户看到自己的选择
                }
            });
            
            // 上一题按钮
            $('#prev-question').click(function(e) {
                e.preventDefault();
                if (currentQuestion > 0) {
                    goToQuestion(currentQuestion - 1);
                }
            });
            
            // 下一题按钮
            $('#next-question').click(function(e) {
                e.preventDefault();
                if (currentQuestion < totalQuestions - 1) {
                    goToQuestion(currentQuestion + 1);
                }
            });
            
            // 显示通知提示
              function showNotification(message, type) {
                  var notification = $('#mbti-notification');
                  notification.find('.mbti-notification-content').text(message);
                  notification.removeClass('mbti-notification-error mbti-notification-success mbti-notification-info');
                  notification.addClass('mbti-notification-' + (type || 'error'));
                  notification.show();
                  
                  // 点击关闭按钮事件
                  notification.find('.mbti-notification-close').off('click').on('click', function() {
                      notification.hide();
                  });
                  
                  // 3秒后自动隐藏
                  setTimeout(function() {
                      notification.fadeOut();
                  }, 3000);
              }
            
            // 提交表单
            $('#mbti-test-form').submit(function(e) {
                e.preventDefault();
                
                // 计算结果
                var scores = {
                    'EI': 0,
                    'SN': 0,
                    'TF': 0,
                    'JP': 0
                };
                
                var answeredQuestions = 0;
                var totalEI = 0, totalSN = 0, totalTF = 0, totalJP = 0;
                var firstUnansweredQuestion = null;
                
                $('.mbti-question').each(function(index) {
                    var type = $(this).data('type');
                    var questionId = $(this).data('question-id');
                    var selectedOption = $('input[name="question_' + questionId + '"]:checked');
                    
                    if (selectedOption.length > 0) {
                        answeredQuestions++;
                        var score = parseInt(selectedOption.val());
                        scores[type] += score;
                        
                        // 统计各维度题目数量
                        switch(type) {
                            case 'EI': totalEI++;
                                break;
                            case 'SN': totalSN++;
                                break;
                            case 'TF': totalTF++;
                                break;
                            case 'JP': totalJP++;
                                break;
                        }
                    } else if (firstUnansweredQuestion === null) {
                        // 记录第一个未回答的题目索引
                        firstUnansweredQuestion = index;
                    }
                });
                
                // 验证是否所有题目都已回答
                if (answeredQuestions < totalQuestions) {
                    showNotification('<?php echo esc_js( __( 'Please answer all questions, jumping to unanswered questions...', 'mbti-test-plugin' ) ); ?>', 'error');
                    
                    // 跳转到第一个未回答的题目
                    if (firstUnansweredQuestion !== null) {
                        setTimeout(function() {
                            goToQuestion(firstUnansweredQuestion);
                            // 为未回答的题目添加高亮提示
                            $('.mbti-question').eq(firstUnansweredQuestion).addClass('mbti-question-unanswered');
                            setTimeout(function() {
                                $('.mbti-question').eq(firstUnansweredQuestion).removeClass('mbti-question-unanswered');
                            }, 2000);
                        }, 1000);
                    }
                    return;
                }
                
                // 确定MBTI类型
                var mbtiType = '';
                
                // EI维度：得分大于等于平均分表示I，否则表示E
                mbtiType += (scores['EI'] >= totalEI / 2) ? 'I' : 'E';
                
                // SN维度：得分大于等于平均分表示N，否则表示S
                mbtiType += (scores['SN'] >= totalSN / 2) ? 'N' : 'S';
                
                // TF维度：得分大于等于平均分表示F，否则表示T
                mbtiType += (scores['TF'] >= totalTF / 2) ? 'F' : 'T';
                
                // JP维度：得分大于等于平均分表示P，否则表示J
                mbtiType += (scores['JP'] >= totalJP / 2) ? 'P' : 'J';
                
                // 获取结果描述
                var resultsData = <?php echo json_encode( $questions['results'] ); ?>;
                // 确保结果数据存在
                if (!resultsData || typeof resultsData !== 'object') {
                    resultsData = {};
                }
                var result = resultsData[mbtiType] || { name: '<?php echo esc_js( __( 'Unknown Type', 'mbti-test-plugin' ) ); ?>', description: '<?php echo esc_js( __( 'Unable to determine your personality type', 'mbti-test-plugin' ) ); ?>' };
                
                // 显示结果
                $('.mbti-result-type').text(mbtiType + ': ' + result.name);
                $('.mbti-result-description').text(result.description);
                
                $('.mbti-test-form').hide();
                $('#mbti-results').show();
            });
            
            // 分享功能
            $('.mbti-share-btn').click(function() {
                var platform = $(this).data('platform');
                var url = window.location.href;
                var title = '<?php echo esc_js( __( 'My MBTI Personality Test Result: ', 'mbti-test-plugin' ) ); ?>' + $('.mbti-result-type').text();
                
                switch(platform) {
                    case 'facebook':
                        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank');
                        break;
                    case 'twitter':
                        window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title), '_blank');
                        break;
                    case 'wechat':
                        alert('<?php echo esc_js( __( 'Please scan QR code to share on WeChat', 'mbti-test-plugin' ) ); ?>');
                        break;
                }
            });
        });
    })(jQuery);
    </script>
    
    <style>
    .mbti-test-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .mbti-test-title {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }
    
    .mbti-upgrade-banner {
        background-color: #fff9c4;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        text-align: center;
        border-left: 4px solid #ffeb3b;
    }
    
    .mbti-progress-container {
        margin-bottom: 20px;
    }
    
    .mbti-progress-bar {
        height: 10px;
        background-color: #e0e0e0;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .mbti-progress-fill {
        height: 100%;
        background-color: #4caf50;
        transition: width 0.3s ease;
    }
    
    .mbti-progress-text {
        font-size: 12px;
        color: #666;
        text-align: right;
    }
    
    .mbti-question {
        margin-bottom: 30px;
        padding: 20px;
        background-color: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .mbti-question-number {
        font-weight: bold;
        color: #4caf50;
        margin-bottom: 10px;
    }
    
    .mbti-question-text {
        font-size: 18px;
        margin-bottom: 15px;
        color: #333;
    }
    
    .mbti-option {
        margin-bottom: 10px;
    }
    
    .mbti-option input[type="radio"] {
        margin-right: 10px;
    }
    
    .mbti-option label {
        cursor: pointer;
        color: #666;
    }
    
    .mbti-test-actions {
        text-align: center;
        margin-top: 30px;
    }
    
    .mbti-submit-btn {
            background-color: #0073aa;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin: 0 10px;
        }
        
        .mbti-submit-btn:hover {
            background-color: #005177;
        }
        
        .mbti-nav-btn {
            background-color: #666;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .mbti-nav-btn:hover:not(:disabled) {
            background-color: #444;
        }
        
        .mbti-nav-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        /* 通知提示样式 */
        .mbti-notification {
            margin: 15px auto;
            padding: 8px 15px;
            border-radius: 4px;
            color: white;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            max-width: 600px;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .mbti-notification-error {
            background-color: #e74c3c;
        }
        
        .mbti-notification-success {
            background-color: #2ecc71;
        }
        
        .mbti-notification-info {
            background-color: #3498db;
        }
        
        .mbti-notification-content {
            flex: 1;
        }
        
        .mbti-notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: 10px;
            padding: 0;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* 未回答题目高亮样式 */
        .mbti-question-unanswered {
            animation: pulse 2s infinite;
            background-color: #fff9c4 !important;
            border: 2px solid #ffeb3b !important;
        }
        
        @keyframes pulse {
            0% {
                background-color: #fff9c4;
                border-color: #ffeb3b;
            }
            50% {
                background-color: #fff59d;
                border-color: #ffc107;
            }
            100% {
                background-color: #fff9c4;
                border-color: #ffeb3b;
            }
        }
    
    .mbti-results {
        text-align: center;
        padding: 30px;
    }
    
    .mbti-results-title {
        color: #333;
        margin-bottom: 20px;
    }
    
    .mbti-result-type {
        font-size: 24px;
        font-weight: bold;
        color: #4caf50;
        margin-bottom: 15px;
    }
    
    .mbti-result-description {
        font-size: 16px;
        color: #666;
        line-height: 1.6;
        margin-bottom: 30px;
    }
    
    .mbti-share-container {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }
    
    .mbti-share-buttons {
        margin-top: 15px;
    }
    
    .mbti-share-btn {
        background-color: #f1f1f1;
        border: none;
        padding: 8px 16px;
        margin: 0 5px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .mbti-share-btn:hover {
        background-color: #e0e0e0;
    }
    
    /* 广告样式 */
    .mbti-ad-container {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }
    
    .mbti-ad-content {
        background-color: #f5f5f5;
        padding: 20px;
        border-radius: 6px;
        text-align: center;
        border: 1px dashed #ddd;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* 升级高级功能广告样式 */
    .mbti-premium-upgrade .mbti-upgrade-banner {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        padding: 25px;
        border-radius: 8px;
        border: 2px solid #ff9800;
        box-shadow: 0 4px 15px rgba(255, 152, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .mbti-premium-upgrade .mbti-upgrade-banner:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 152, 0, 0.15);
    }
    
    .mbti-premium-upgrade ul {
        list-style-type: none !important;
        padding-left: 20px !important;
    }
    
    .mbti-premium-upgrade ul li {
        position: relative;
        padding-left: 25px !important;
        margin-bottom: 8px !important;
        font-size: 14px !important;
        line-height: 1.4 !important;
    }
    
    .mbti-premium-upgrade ul li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #ff9800;
        font-weight: bold;
    }
    
    .mbti-upgrade-btn {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 15px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
    }
    
    .mbti-upgrade-btn:hover {
        background: linear-gradient(135deg, #f57c00 0%, #ef6c00 100%);
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
    }
    
    .mbti-upgrade-btn:active {
        transform: scale(0.98);
    }
    
    .mbti-ad-content p {
        color: #888;
        font-style: italic;
        margin: 0;
    }
    </style>
    
    <?php
    return ob_get_clean();
}