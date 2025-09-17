/**
 * MBTI Test Plugin - JavaScript
 * 提供MBTI测试的交互功能
 */

(function($) {
    'use strict';

    // 全局变量
    let currentQuestion = 0;
    let totalQuestions = 0;
    let answers = {};

    // 初始化
    $(document).ready(function() {
        initMBTITest();
    });

    /**
     * 初始化MBTI测试
     */
    function initMBTITest() {
        console.log('🚀 initMBTITest 开始初始化');
        
        const $questions = $('.mbti-question-card, .mbti-question');
        totalQuestions = $questions.length;
        
        console.log('找到问题数量:', totalQuestions);
        console.log('问题元素:', $questions);

        if (totalQuestions === 0) {
            console.error('❌ 没有找到问题元素');
            return;
        }

        // 初始化显示
        initQuestionDisplay();
        
        // 绑定事件
        bindEvents();
        
        // 显示第一个问题
        showQuestion(0);
        
        // 初始化选项样式
        initOptionStyles();
        
        // 更新进度
        updateProgress();
        
        console.log('✅ MBTI测试初始化完成');
    }

    /**
     * 初始化问题显示
     */
    function initQuestionDisplay() {
        const $questions = $('.mbti-question-card, .mbti-question');
        
        // 隐藏所有问题
        $questions.hide();
        
        // 为每个问题添加索引
        $questions.each(function(index) {
            $(this).attr('data-question-index', index);
        });
    }

    /**
     * 绑定事件处理器
     */
    function bindEvents() {
        console.log('🔗 开始绑定事件');
        
        // 检查关键元素
        const $form = $('#mbti-test-form');
        const $submitBtn = $('.mbti-submit-btn');
        const $prevBtn = $('#prev-question');
        const $nextBtn = $('#next-question');
        
        console.log('表单元素:', $form.length);
        console.log('提交按钮:', $submitBtn.length);
        console.log('上一题按钮:', $prevBtn.length);
        console.log('下一题按钮:', $nextBtn.length);
        
        // 选项选择事件
        $(document).on('change', '.mbti-option-input, input[type="radio"]', handleOptionChange);
        
        // 导航按钮事件
        $prevBtn.on('click', function(e) {
            console.log('🔙 上一题按钮被点击');
            goToPreviousQuestion();
        });
        
        $nextBtn.on('click', function(e) {
            console.log('🔜 下一题按钮被点击');
            goToNextQuestion();
        });
        
        // 表单提交事件 - 多重绑定确保触发
        $form.on('submit', function(e) {
            console.log('📝 表单submit事件被触发');
            e.preventDefault();
            handleFormSubmit(e);
        });
        
        $(document).on('click', '.mbti-submit-btn', function(e) {
            console.log('🎯 委托绑定的提交按钮被点击');
            e.preventDefault();
            e.stopPropagation();
            handleFormSubmit(e);
        });
        
        // 额外的提交按钮绑定
        $submitBtn.on('click', function(e) {
            console.log('🎯 直接绑定的提交按钮被点击');
            e.preventDefault();
            e.stopPropagation();
            handleFormSubmit(e);
        });
        
        // 通知关闭事件
        $('.mbti-notification-close').on('click', hideNotification);
        
        // 选项卡片点击事件
        $(document).on('click', '.mbti-option-card', function() {
            const $input = $(this).find('.mbti-option-input, input[type="radio"]');
            if ($input.length && !$input.prop('checked')) {
                $input.prop('checked', true).trigger('change');
            }
        });
        
        console.log('✅ 事件绑定完成');
        
        // 添加更强的事件绑定 - 确保手动点击也能工作
        setTimeout(function() {
            const $submitBtn = $('.mbti-submit-btn');
            console.log('🔧 开始强化事件绑定，按钮数量:', $submitBtn.length);
            
            // 移除所有现有事件，重新绑定
            $submitBtn.off('click').on('click', function(e) {
                console.log('🎯 强化绑定的提交按钮被点击 - 手动点击');
                e.preventDefault();
                e.stopPropagation();
                handleFormSubmit(e);
                return false;
            });
            
            // 添加mousedown事件作为备用
            $submitBtn.on('mousedown', function(e) {
                console.log('🖱️ 提交按钮mousedown事件');
            });
            
            // 添加mouseup事件
            $submitBtn.on('mouseup', function(e) {
                console.log('🖱️ 提交按钮mouseup事件');
            });
            
            // 添加touchstart事件支持移动设备
            $submitBtn.on('touchstart', function(e) {
                console.log('📱 提交按钮touchstart事件');
                e.preventDefault();
                handleFormSubmit(e);
            });
            
            // 测试按钮是否可以被找到和绑定
            if ($submitBtn.length > 0) {
                console.log('✅ 找到提交按钮，强化绑定成功');
                console.log('按钮元素:', $submitBtn[0]);
                console.log('按钮类名:', $submitBtn[0].className);
            } else {
                console.error('❌ 未找到提交按钮');
            }
            
            console.log('🔧 强化事件绑定完成');
        }, 1000);
    }

    /**
     * 处理选项变化
     */
    function handleOptionChange(e) {
        const $input = $(e.target);
        const questionName = $input.attr('name');
        const questionId = questionName.replace('question_', '');
        const value = $input.val();

        // 保存答案
        answers[questionId] = value;

        // 更新选项卡片的选中状态样式
        updateOptionStyles($input);

        // 更新进度
        updateProgress();
        
        // 更新导航按钮状态
        updateNavigationButtons();
        
        // 隐藏通知（如果有的话）
        hideNotification();
        
        // 添加选择效果
        const $card = $input.closest('.mbti-option-card, .mbti-option');
        if ($card.length) {
            $card.addClass('selected-animation');
            setTimeout(() => {
                $card.removeClass('selected-animation');
            }, 300);
        }

        // 自动跳转到下一题
        if (currentQuestion < totalQuestions - 1) {
            setTimeout(() => {
                showQuestion(currentQuestion + 1);
            }, 500);
        }
    }

    /**
     * 初始化选项样式
     */
    function initOptionStyles() {
        $('input[type="radio"]:checked').each(function() {
            updateOptionStyles($(this));
        });
    }

    /**
     * 更新选项样式
     */
    function updateOptionStyles($selectedInput) {
        const questionName = $selectedInput.attr('name');
        
        // 移除同一问题下所有选项的选中样式
        $(`input[name="${questionName}"]`).each(function() {
            const $input = $(this);
            const $card = $input.closest('.mbti-option-card, .mbti-option');
            const $content = $input.siblings('.mbti-option-content');
            const $indicator = $content.find('.mbti-option-indicator');
            
            $card.removeClass('mbti-selected');
            $content.removeClass('mbti-selected');
            $indicator.removeClass('mbti-selected');
        });
        
        // 为选中的选项添加样式
        const $selectedCard = $selectedInput.closest('.mbti-option-card, .mbti-option');
        const $selectedContent = $selectedInput.siblings('.mbti-option-content');
        const $selectedIndicator = $selectedContent.find('.mbti-option-indicator');
        
        $selectedCard.addClass('mbti-selected');
        $selectedContent.addClass('mbti-selected');
        $selectedIndicator.addClass('mbti-selected');
    }

    /**
     * 显示指定问题
     */
    function showQuestion(index) {
        if (index < 0 || index >= totalQuestions) return;

        const $questions = $('.mbti-question-card, .mbti-question');
        const $currentQuestion = $questions.eq(index);

        // 隐藏所有问题
        $questions.hide().removeClass('active');
        
        // 显示当前问题
        $currentQuestion.show().addClass('active');
        
        // 更新当前问题索引
        currentQuestion = index;
        
        // 更新导航按钮
        updateNavigationButtons();
        
        // 更新进度
        updateProgress();
        
        // 滚动到问题顶部
        scrollToQuestion();
    }

    /**
     * 滚动到当前问题
     */
    function scrollToQuestion() {
        const $container = $('.mbti-test-container, .mbti-test-wrapper');
        if ($container.length > 0) {
            $('html, body').animate({
                scrollTop: $container.offset().top - 50
            }, 300);
        }
    }

    /**
     * 上一题
     */
    function goToPreviousQuestion() {
        if (currentQuestion > 0) {
            showQuestion(currentQuestion - 1);
        }
    }

    /**
     * 下一题
     */
    function goToNextQuestion() {
        if (currentQuestion < totalQuestions - 1) {
            showQuestion(currentQuestion + 1);
        }
    }

    /**
     * 更新导航按钮状态
     */
    function updateNavigationButtons() {
        const $prevBtn = $('#prev-question');
        const $nextBtn = $('#next-question');
        const $submitBtn = $('.mbti-submit-btn');

        // 上一题按钮
        $prevBtn.prop('disabled', currentQuestion === 0);
        
        // 下一题按钮
        $nextBtn.prop('disabled', currentQuestion === totalQuestions - 1);
        
        // 按钮显示逻辑
        if (currentQuestion === totalQuestions - 1) {
            // 最后一题：隐藏下一题按钮，显示提交按钮
            $nextBtn.hide();
            $submitBtn.show();
            $submitBtn.prop('disabled', false);
        } else {
            // 非最后一题：显示下一题按钮，隐藏提交按钮
            $nextBtn.show();
            $submitBtn.hide();
        }
    }

    /**
     * 更新进度条
     */
    function updateProgress() {
        const answeredCount = Object.keys(answers).length;
        const progress = totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0;
        
        // 更新进度条
        $('.mbti-progress-fill').css('width', progress + '%');
        
        // 更新进度文本
        $('.mbti-progress-text').text(`${answeredCount}/${totalQuestions}`);
        
        // 更新问题编号显示
        $('.mbti-question-badge').text(currentQuestion + 1);
    }

    /**
     * 检查测试是否完成
     */
    function isTestComplete() {
        const $questions = $('.mbti-question-card, .mbti-question');
        const actualTotalQuestions = $questions.length;
        const answeredCount = Object.keys(answers).length;
        
        return answeredCount === actualTotalQuestions;
    }

    /**
     * 获取未完成问题的详细信息
     */
    function getIncompleteQuestionInfo() {
        const $questions = $('.mbti-question-card, .mbti-question');
        let firstIncompleteIndex = -1;
        let uncompletedCount = 0;
        let allQuestionIds = [];
        
        $questions.each(function(index) {
            const questionId = $(this).data('question-id');
            allQuestionIds.push(questionId);
            
            const hasAnswer = answers[questionId] !== undefined && answers[questionId] !== null && answers[questionId] !== '';
            
            if (!hasAnswer) {
                uncompletedCount++;
                if (firstIncompleteIndex === -1) {
                    firstIncompleteIndex = index;
                }
            }
        });
        
        return {
            hasIncomplete: uncompletedCount > 0,
            firstIncompleteIndex: firstIncompleteIndex,
            uncompletedCount: uncompletedCount,
            allQuestionIds: allQuestionIds
        };
    }

    /**
     * 显示验证警告
     */
    function showValidationWarning(message, jumpToQuestionIndex) {
        console.log('🚨 showValidationWarning 被调用');
        console.log('消息:', message);
        console.log('跳转索引:', jumpToQuestionIndex);
        
        // 隐藏现有通知
        hideNotification();
        
        // 移除现有的警告元素
        $('.mbti-validation-warning').remove();
        console.log('已移除现有警告元素');
        
        // 创建新的警告元素，添加强制显示样式
        const $warning = $(`
            <div class="mbti-validation-warning" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                <div class="mbti-warning-content">
                    <div class="mbti-warning-icon">⚠️</div>
                    <div class="mbti-warning-text">${message}</div>
                    <button type="button" class="mbti-warning-close">×</button>
                </div>
            </div>
        `);
        
        console.log('创建的警告元素:', $warning[0]);
        
        // 查找多个可能的插入位置
        const $navigation = $('.mbti-navigation');
        const $submitBtn = $('.mbti-submit-btn');
        const $container = $('#mbti-test-form .mbti-questions-container, .mbti-test-container, .mbti-questions-container');
        const $form = $('#mbti-test-form');
        
        console.log('导航区域数量:', $navigation.length);
        console.log('提交按钮数量:', $submitBtn.length);
        console.log('容器数量:', $container.length);
        console.log('表单数量:', $form.length);
        
        let inserted = false;
        
        // 尝试插入到提交按钮前面
        if ($submitBtn.length > 0) {
            $submitBtn.before($warning);
            console.log('✅ 警告已插入到提交按钮前面');
            inserted = true;
        }
        // 尝试插入到导航区域前面
        else if ($navigation.length > 0) {
            $navigation.before($warning);
            console.log('✅ 警告已插入到导航区域前面');
            inserted = true;
        }
        // 尝试插入到容器后面
        else if ($container.length > 0) {
            $container.after($warning);
            console.log('✅ 警告已插入到容器后面');
            inserted = true;
        }
        // 最后尝试插入到表单内部
        else if ($form.length > 0) {
            $form.append($warning);
            console.log('✅ 警告已插入到表单内部');
            inserted = true;
        }
        
        if (!inserted) {
            console.error('❌ 无法找到合适的插入位置');
        }
        
        // 检查插入后的状态
        setTimeout(() => {
            const $insertedWarning = $('.mbti-validation-warning');
            console.log('插入后警告元素数量:', $insertedWarning.length);
            console.log('插入后警告是否可见:', $insertedWarning.is(':visible'));
            console.log('插入后警告CSS display:', $insertedWarning.css('display'));
            console.log('插入后警告位置:', $insertedWarning.offset());
        }, 100);
        
        // 绑定关闭事件
        $warning.find('.mbti-warning-close').on('click', function() {
            $warning.fadeOut(300);
        });
        
        // 跳转到未完成的问题
        if (jumpToQuestionIndex >= 0) {
            setTimeout(() => {
                showQuestion(jumpToQuestionIndex);
                
                // 高亮显示未回答的问题
                const $targetQuestion = $('.mbti-question-card, .mbti-question').eq(jumpToQuestionIndex);
                $targetQuestion.addClass('mbti-question-highlight');
                
                // 3秒后移除高亮
                setTimeout(() => {
                    $targetQuestion.removeClass('mbti-question-highlight');
                }, 3000);
                
                // 滚动到问题位置
                scrollToQuestion();
                
            }, 1000);
        }
        
        // 5秒后自动隐藏警告
        setTimeout(() => {
            $warning.fadeOut(300);
        }, 5000);
    }

    /**
     * 处理表单提交
     */
    function handleFormSubmit(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        console.log('🔥 handleFormSubmit 被调用');
        
        // 检查是否所有问题都已回答
        const incompleteInfo = getIncompleteQuestionInfo();
        console.log('未完成信息:', incompleteInfo);
        
        if (incompleteInfo.hasIncomplete) {
            console.log('🚨 发现未完成题目，准备显示警告');
            
            // 显示告警提示在按钮上方
            const warningMessage = `请回答所有问题后再提交。还有 ${incompleteInfo.uncompletedCount} 道题未回答，正在跳转到第 ${incompleteInfo.firstIncompleteIndex + 1} 题...`;
            console.log('警告消息:', warningMessage);
            
            showValidationWarning(warningMessage, incompleteInfo.firstIncompleteIndex);
            
            return false;
        }
        
        console.log('✅ 所有题目已完成，开始提交');

        // 更新提交按钮状态
        const $submitBtn = $('.mbti-submit-btn');
        const originalText = $submitBtn.html();
        $submitBtn.html('<span class="mbti-loading"></span> ' + (mbtiTranslations.submitting || '正在提交...'))
                  .prop('disabled', true);

        // 计算MBTI结果
        const result = calculateMBTIResult();
        
        // 模拟提交延迟
        setTimeout(() => {
            displayResults(result);
            
            // 重置提交状态
            $submitBtn.html(originalText).prop('disabled', false);
            
        }, 1500);
        
        return false;
    }

    /**
     * 计算MBTI结果
     */
    function calculateMBTIResult() {
        const scores = { E: 0, I: 0, S: 0, N: 0, T: 0, F: 0, J: 0, P: 0 };
        
        // 遍历所有答案计算得分
        $('.mbti-question-card, .mbti-question').each(function() {
            const questionId = $(this).data('question-id');
            const dimension = $(this).data('type');
            const answer = answers[questionId];
            
            if (answer && dimension) {
                const score = parseInt(answer);
                if (score > 0) {
                    scores[dimension] += score;
                } else {
                    // 负分给对立维度
                    const opposite = getOppositeDimension(dimension);
                    scores[opposite] += Math.abs(score);
                }
            }
        });
        
        // 确定MBTI类型
        const type = 
            (scores.E > scores.I ? 'E' : 'I') +
            (scores.S > scores.N ? 'S' : 'N') +
            (scores.T > scores.F ? 'T' : 'F') +
            (scores.J > scores.P ? 'J' : 'P');
        
        return {
            type: type,
            scores: scores,
            description: getMBTIDescription(type)
        };
    }

    /**
     * 获取对立维度
     */
    function getOppositeDimension(dimension) {
        const opposites = {
            'E': 'I', 'I': 'E',
            'S': 'N', 'N': 'S',
            'T': 'F', 'F': 'T',
            'J': 'P', 'P': 'J'
        };
        return opposites[dimension] || dimension;
    }

    /**
     * 获取MBTI类型描述
     */
    function getMBTIDescription(type) {
        const descriptions = {
            'INTJ': '建筑师 - 富有想象力和战略性的思想家，一切皆在计划之中。',
            'INTP': '逻辑学家 - 具有创造性的发明家，对知识有着止不住的渴望。',
            'ENTJ': '指挥官 - 大胆，富有想象力，意志强烈的领导者，总能找到或创造解决方法。',
            'ENTP': '辩论家 - 聪明好奇的思想家，不会放弃任何挑战。',
            'INFJ': '提倡者 - 安静而神秘，同时鼓舞他人的理想主义者。',
            'INFP': '调停者 - 诗意，善良的利他主义者，总是热情地为正当理由而努力。',
            'ENFJ': '主人公 - 富有魅力鼓舞他人的领导者，让听众着迷。',
            'ENFP': '竞选者 - 热情，有创造力，社交能力强，总能找到微笑的理由。',
            'ISTJ': '物流师 - 实用主义的现实主义者，喜欢事实胜过想象。',
            'ISFJ': '守护者 - 非常专注而温暖的守护者，时刻准备保护爱着的人们。',
            'ESTJ': '总经理 - 出色的管理者，在管理事情或人的时候无与伦比。',
            'ESFJ': '执政官 - 极有同情心，善于交际，在团体中深受欢迎。',
            'ISTP': '鉴赏家 - 大胆而实际的实验家，擅长使用各种工具。',
            'ISFP': '探险家 - 灵活有魅力的艺术家，时刻准备探索新的可能性。',
            'ESTP': '企业家 - 聪明，精力充沛，非常善于感知，真正享受生活在边缘。',
            'ESFP': '娱乐家 - 自发的，精力充沛和热情的表演者，生活对他们来说从不无聊。'
        };
        
        return descriptions[type] || '独特的个性类型，具有自己的特色和优势。';
    }

    /**
     * 显示测试结果
     */
    function displayResults(result) {
        // 隐藏测试表单
        $('#mbti-test-form').fadeOut(300, function() {
            // 显示结果区域
            $('#mbti-results').fadeIn(500);
            
            // 填充结果内容
            $('.mbti-result-type').html(`
                <div class="mbti-type-badge">${result.type}</div>
            `);
            
            $('.mbti-result-description').html(`
                <p class="mbti-description-text">${result.description}</p>
            `);
            
            // 滚动到结果区域
            setTimeout(() => {
                $('html, body').animate({
                    scrollTop: $('#mbti-results').offset().top - 50
                }, 500);
            }, 300);
        });
    }

    /**
     * 显示通知
     */
    function showNotification(message, type = 'info') {
        const $notification = $('#mbti-notification');
        const $content = $('.mbti-notification-content');
        
        if ($notification.length && $content.length) {
            // 设置消息内容
            $content.text(message);
            
            // 设置通知类型样式
            $notification.removeClass('mbti-notification-error mbti-notification-warning mbti-notification-success')
                        .addClass(`mbti-notification-${type}`);
            
            // 显示通知
            $notification.slideDown(300);
            
            // 自动隐藏（除了错误消息）
            if (type !== 'error') {
                setTimeout(() => {
                    hideNotification();
                }, 3000);
            }
        }
    }

    /**
     * 隐藏通知
     */
    function hideNotification() {
        $('#mbti-notification').slideUp(300);
    }

    // 添加CSS动画类
    const style = document.createElement('style');
    style.textContent = `
        .selected-animation {
            animation: selectedPulse 0.3s ease;
        }
        
        @keyframes selectedPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        .mbti-type-badge {
            display: inline-block;
            font-size: 3em;
            font-weight: 800;
            color: #667eea;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .mbti-loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        

    `;
    document.head.appendChild(style);

})(jQuery);