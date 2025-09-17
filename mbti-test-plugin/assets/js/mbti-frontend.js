/**
 * MBTI 测试前端 JavaScript
 * 处理语言切换、测试逻辑和用户交互
 */

(function($) {
    'use strict';
    
    // 全局变量
    var mbtiTest = {
        currentQuestion: 0,
        totalQuestions: 0,
        answers: {},
        isSubmitting: false,
        currentLanguage: 'en_US'
    };
    
    // 初始化
    $(document).ready(function() {
        initializeMBTITest();
        initializeLanguageSwitcher();
        initializeNotificationSystem();
    });
    
    /**
     * 初始化 MBTI 测试
     */
    function initializeMBTITest() {
        mbtiTest.totalQuestions = $('.mbti-question').length;
        
        if (mbtiTest.totalQuestions > 0) {
            showQuestion(0);
            updateProgress();
            bindTestEvents();
        }
    }
    
    /**
     * 初始化语言切换器
     */
    function initializeLanguageSwitcher() {
        var $languageSelect = $('#mbti-language-select');
        
        if ($languageSelect.length) {
            // 保存原始值
            $languageSelect.data('original-value', $languageSelect.val());
            mbtiTest.currentLanguage = $languageSelect.val();
            
            // 绑定切换事件
            $languageSelect.on('change', handleLanguageSwitch);
        }
    }
    
    /**
     * 初始化通知系统
     */
    function initializeNotificationSystem() {
        // 创建通知容器（如果不存在）
        if ($('#mbti-notification').length === 0) {
            $('body').append('<div id="mbti-notification" class="mbti-notification"><div class="mbti-notification-content"></div><button type="button" class="mbti-notification-close">×</button></div>');
        }
        
        // 绑定关闭事件
        $(document).on('click', '.mbti-notification-close', function() {
            hideNotification();
        });
        
        // 自动隐藏通知
        $(document).on('click', '.mbti-notification', function(e) {
            if (e.target === this) {
                hideNotification();
            }
        });
    }
    
    /**
     * 绑定测试相关事件
     */
    function bindTestEvents() {
        // 上一题按钮
        $('#prev-question').on('click', function() {
            if (mbtiTest.currentQuestion > 0) {
                showQuestion(mbtiTest.currentQuestion - 1);
            }
        });
        
        // 下一题按钮
        $('#next-question').on('click', function() {
            if (mbtiTest.currentQuestion < mbtiTest.totalQuestions - 1) {
                showQuestion(mbtiTest.currentQuestion + 1);
            }
        });
        
        // 选项选择事件
        $('.mbti-option input[type="radio"]').on('change', function() {
            var questionId = $(this).closest('.mbti-question').data('question-id');
            mbtiTest.answers[questionId] = $(this).val();
            updateProgress();
            
            // 自动跳转到下一题（可选）
            setTimeout(function() {
                if (mbtiTest.currentQuestion < mbtiTest.totalQuestions - 1) {
                    showQuestion(mbtiTest.currentQuestion + 1);
                }
            }, 500);
        });
        
        // 提交测试
        $('#mbti-test-form').on('submit', function(e) {
            e.preventDefault();
            submitTest();
        });
    }
    
    /**
     * 显示指定题目
     */
    function showQuestion(index) {
        if (index < 0 || index >= mbtiTest.totalQuestions) {
            return;
        }
        
        // 隐藏所有题目
        $('.mbti-question').hide();
        
        // 显示当前题目
        $('.mbti-question').eq(index).show();
        
        // 更新当前题目索引
        mbtiTest.currentQuestion = index;
        
        // 更新按钮状态
        $('#prev-question').prop('disabled', index === 0);
        $('#next-question').prop('disabled', index === mbtiTest.totalQuestions - 1);
        
        // 更新进度
        updateProgress();
    }
    
    /**
     * 更新进度条
     */
    function updateProgress() {
        var answeredQuestions = Object.keys(mbtiTest.answers).length;
        var progress = (answeredQuestions / mbtiTest.totalQuestions) * 100;
        
        $('.mbti-progress-fill').css('width', progress + '%');
        $('.mbti-progress-text').text(answeredQuestions + '/' + mbtiTest.totalQuestions);
    }
    
    /**
     * 处理语言切换
     */
    function handleLanguageSwitch() {
        var $select = $(this);
        var selectedLanguage = $select.val();
        var $loading = $('.mbti-language-loading');
        
        if (selectedLanguage === mbtiTest.currentLanguage) {
            return;
        }
        
        // 显示加载状态
        $loading.show();
        $select.prop('disabled', true);
        
        showNotification(mbtiTranslations.switching_language, 'info');
        
        // 发送 AJAX 请求
        $.post(mbtiAjax.ajaxurl, {
            action: 'mbti_switch_language',
            language: selectedLanguage,
            nonce: mbtiAjax.nonce
        })
        .done(function(response) {
            if (response.success) {
                // 更新测试内容
                updateTestContent(response.data);
                
                // 更新当前语言
                mbtiTest.currentLanguage = selectedLanguage;
                
                // 显示成功消息
                showNotification(response.data.ui_texts.language_switched, 'success');
                
                // 保存新的原始值
                $select.data('original-value', selectedLanguage);
            } else {
                // 显示错误消息
                showNotification(response.data.message || mbtiTranslations.switch_error, 'error');
                
                // 恢复原始选择
                $select.val($select.data('original-value'));
            }
        })
        .fail(function() {
            showNotification(mbtiTranslations.network_error, 'error');
            $select.val($select.data('original-value'));
        })
        .always(function() {
            // 隐藏加载状态
            $loading.hide();
            $select.prop('disabled', false);
        });
    }
    
    /**
     * 更新测试内容
     */
    function updateTestContent(data) {
        var questions = data.questions;
        var uiTexts = data.ui_texts;
        
        // 更新测试标题
        $('.mbti-test-title').text(uiTexts.test_title);
        
        // 更新题目内容
        if (questions && questions.questions) {
            questions.questions.forEach(function(question, index) {
                var $questionDiv = $('.mbti-question[data-question-id="' + question.id + '"]');
                if ($questionDiv.length) {
                    // 更新题目编号
                    $questionDiv.find('.mbti-question-number').text(uiTexts.question_label.replace('%d', index + 1));
                    
                    // 更新题目文本
                    $questionDiv.find('.mbti-question-text').text(question.text);
                    
                    // 更新选项文本
                    question.options.forEach(function(option, optionIndex) {
                        $questionDiv.find('.mbti-option').eq(optionIndex).find('label').text(option.text);
                    });
                }
            });
        }
        
        // 更新按钮文本
        $('#prev-question').text(uiTexts.previous_question);
        $('#next-question').text(uiTexts.next_question);
        $('.mbti-submit-btn').text(uiTexts.submit_test);
        
        // 更新结果区域文本
        $('.mbti-results-title').text(uiTexts.results_title);
        
        // 更新分享按钮文本
        $('.mbti-share-btn[data-platform="facebook"]').text(uiTexts.facebook);
        $('.mbti-share-btn[data-platform="twitter"]').text(uiTexts.twitter);
        $('.mbti-share-btn[data-platform="wechat"]').text(uiTexts.wechat);
        
        // 更新语言切换器标签
        $('.mbti-language-switcher label').text(uiTexts.language_switcher);
    }
    
    /**
     * 提交测试
     */
    function submitTest() {
        if (mbtiTest.isSubmitting) {
            return;
        }
        
        // 检查是否所有题目都已回答
        if (Object.keys(mbtiTest.answers).length < mbtiTest.totalQuestions) {
            showNotification(mbtiTranslations.incomplete_test, 'error');
            return;
        }
        
        mbtiTest.isSubmitting = true;
        
        // 显示加载状态
        $('.mbti-submit-btn').html(mbtiTranslations.submitting + ' <span class="mbti-loading"></span>').prop('disabled', true);
        
        // 计算结果
        var result = calculateMBTIResult(mbtiTest.answers);
        
        // 模拟提交延迟
        setTimeout(function() {
            displayResult(result);
            mbtiTest.isSubmitting = false;
            $('.mbti-submit-btn').text(mbtiTranslations.submit_test).prop('disabled', false);
        }, 1500);
    }
    
    /**
     * 计算 MBTI 结果
     */
    function calculateMBTIResult(answers) {
        var scores = { E: 0, I: 0, S: 0, N: 0, T: 0, F: 0, J: 0, P: 0 };
        
        // 根据答案计算各维度得分
        $('.mbti-question').each(function() {
            var questionId = $(this).data('question-id');
            var questionType = $(this).data('type');
            var answer = parseInt(answers[questionId]);
            
            if (questionType && answer !== undefined) {
                if (answer === 0) {
                    scores[questionType.charAt(0)]++;
                } else {
                    scores[questionType.charAt(1)]++;
                }
            }
        });
        
        // 确定性格类型
        var type = '';
        type += scores.E > scores.I ? 'E' : 'I';
        type += scores.S > scores.N ? 'S' : 'N';
        type += scores.T > scores.F ? 'T' : 'F';
        type += scores.J > scores.P ? 'J' : 'P';
        
        return {
            type: type,
            scores: scores
        };
    }
    
    /**
     * 显示测试结果
     */
    function displayResult(result) {
        // 隐藏测试表单
        $('#mbti-test-form').hide();
        
        // 显示结果
        var $results = $('#mbti-results');
        $results.find('.mbti-result-type').text(result.type);
        
        // 这里应该根据结果类型显示相应的描述
        // 暂时显示简单的结果
        $results.find('.mbti-result-description').text('您的性格类型是 ' + result.type + '。这是一个基于您的回答计算出的结果。');
        
        $results.show();
        
        // 滚动到结果区域
        $('html, body').animate({
            scrollTop: $results.offset().top - 50
        }, 500);
        
        showNotification(mbtiTranslations.test_completed, 'success');
    }
    
    /**
     * 显示通知
     */
    function showNotification(message, type) {
        type = type || 'info';
        
        var $notification = $('#mbti-notification');
        var $content = $notification.find('.mbti-notification-content');
        
        $content.text(message);
        $notification.removeClass('mbti-success mbti-error mbti-info').addClass('mbti-' + type + ' show');
        
        // 3秒后自动隐藏
        setTimeout(function() {
            hideNotification();
        }, 3000);
    }
    
    /**
     * 隐藏通知
     */
    function hideNotification() {
        $('#mbti-notification').removeClass('show');
    }
    
    // 导出到全局作用域（用于其他脚本调用）
    window.mbtiShowNotification = showNotification;
    window.mbtiUpdateTestContent = updateTestContent;
    
})(jQuery);