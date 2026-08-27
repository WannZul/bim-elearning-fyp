<?php
require_once __DIR__ . '/../../includes/sign_catalog.php';

$copy = [
    'dashboard' => ['completed' => '已完成测验', 'progress_intro' => '字母与数字理论测验成绩会分别保存。实验性摄像头练习结果不会保存。', 'six_lessons' => '37 个学习手势', 'ten_minutes' => 'A–Z 与 0–10', 'five_signs' => '19 个兼容手势', 'five_ai' => '19 项摄像头练习', 'module1_desc' => '学习完整的单手 BIM 字母 A–Z 与数字 0–10 课程。', 'module2_desc' => '练习此关键点原型兼容的 19 个静态手势。', 'basics_desc' => '学习全部 37 个手势，再使用摄像头练习其中 19 个兼容手势。', 'recent' => '最近的理论尝试', 'recent_intro' => '理论成绩、类别、用时与日期均会记录。', 'no_records' => '暂无理论记录', 'no_records_desc' => '完成字母或数字理论测验后即可追踪进度。', 'quiz_type' => ':type'],
    'learn' => [
        'intro' => '学习全部 37 个单手 BIM 字母与数字手势。摄像头练习仅支持兼容的静态手势。', 'path_desc' => '完整 A–Z 与 0–10 课程，附动态和摄像头兼容说明。', 'lessons_available' => '提供 37 个手势', 'ai_verified' => '19 个摄像头兼容手势', 'first_signs' => '全部 BIM 字母与数字手势', 'duration' => '37 张简洁参考卡',
        'all_count' => '全部 · 37', 'alphabet_count' => '字母 · 26', 'numbers_count' => '数字 · 11', 'static_badge' => '静态', 'dynamic_badge' => '动态', 'camera_badge' => '摄像头兼容', 'reference_badge' => '仅供参考', 'one_hand_badge' => '单手',
        'unavailable_label' => '无法使用摄像头：',
        'unavailable' => [
            'dynamic' => '此手势包含动作；原型仅检查静态手形。',
            'orientation' => '此手势依赖准确的手掌或手指方向，摄像头关键点目前无法可靠区分。',
            'finger_contact' => '此手势依赖细小的指尖或拇指接触，在摄像头中可能被遮挡或显得模糊。',
            'finger_bend' => '此手势依赖准确的手指弯曲，关键点原型可能将其误判为手指折叠。',
            'thumb_placement' => '此手势依赖准确或部分隐藏的拇指位置，摄像头关键点目前无法可靠确认。',
            'finger_crossing' => '此手势依赖交叉手指的前后深度，平面摄像头关键点无法可靠确认。',
            'fine_detail' => '关键点目前无法可靠确认此手势的细微手形。',
        ],
        'movement' => ['J' => '用手形描出 J。', 'Z' => '用手形描出 Z。', '10' => '旋转竖起拇指的手形。'], 'ready_desc' => '使用单手 MediaPipe 关键点与 Fingerpose 练习 19 个指定静态手势。',
        'prototype' => '学习参考：MFD BIM 手语资料库。来源仓库没有许可证，因此不复制第三方图片。', 'reference_link' => '打开 MFD BIM 手语资料库', 'practice_aria' => '使用摄像头练习:title',
    ],
    'ai' => ['intro' => '选择字母或数字，再练习 19 个指定静态原型手势之一。系统只在所选类别中分类。', 'category_label' => '选择手势类别', 'alphabet' => '字母', 'numbers' => '数字', 'alphabet_count' => '9 个兼容字母', 'numbers_count' => '10 个兼容数字', 'target_heading' => ':category目标', 'target_count' => '可选 :count 个目标', 'equivalent_note' => '部分字母与数字手势外形相同。原型会按所选类别进行解释。', 'validation_note' => 'A 与数字 1–4 已获得积极的用户试用反馈。新增手势仍属于实验候选，需继续在更多用户、光线、距离、背景和左右手条件下测试。', 'instruction' => '清楚做出所选静态手势并保持，直到浏览器确认。', 'js' => ['category_changed' => '类别已切换为:category', 'title_sign' => ':category :target']],
    'quiz' => ['hub_intro' => '选择 60 秒理论测验或 90 秒低风险摄像头实践。', 'theory_heading' => '理论测验', 'theory_intro' => '回答五道随机知识题。', 'practical_heading' => '摄像头练习原型', 'practical_intro' => '展示五个不同的兼容手势。结果仅作为低风险个人练习，并非安全的正式考核或排名依据。', 'practical_card_meta' => '5 个摄像头目标', 'practical_choose' => '开始摄像头练习', 'practical_duration' => '90 秒', 'scope' => '课程涵盖 A–Z 与 0–10。引导式摄像头练习提供 19 个指定候选；在完成握拳手形混淆测试记录前，临时无分数挑战不包含 A。'],
    'practical' => [
        'page_title' => ':category摄像头实践', 'eyebrow' => '低风险浏览器确认', 'heading' => ':category实践挑战', 'intro' => '在 90 秒内确认五个不同手势。摄像头只需启动一次；保持目标手势，或跳过并继续。',
        'trust' => '此实验性关键点挑战仅用于个人学习。结果是临时的，不会保存，也不能作为安全的正式考核证据。', 'privacy' => '应用不会主动上传或保存视频和图片。识别在浏览器中运行，并使用从 jsDelivr 加载的固定版本 MediaPipe 与 Fingerpose 脚本；仅提交已确认目标名称用于临时回顾。',
        'camera_label' => '实践挑战摄像头', 'progress' => '目标 :current / :total', 'next_target' => '目标 :current / :total：:title（:target）。', 'confirmed_count' => '已确认 :count / :total', 'time_remaining' => '剩余时间', 'current_target' => '当前目标', 'start' => '启动摄像头与挑战', 'stop' => '停止摄像头', 'skip' => '跳过此目标', 'submit' => '完成并查看回顾', 'waiting' => '准备好后启动摄像头。', 'activating' => '摄像头已就绪，正在启动 90 秒计时。',
        'confirmed' => ':target 已确认。进入下一个目标。', 'skipped' => '已跳过 :target。', 'timeout' => '时间到。正在提交已确认手势。', 'submitting' => '正在提交实践结果……', 'camera_required' => '此实践需要摄像头权限。', 'invalid' => '实践会话无效或已过期，请重新开始。',
        'review_title' => '练习回顾', 'review_heading' => '浏览器报告的个人练习结果', 'review_summary' => '在 :time 内确认了 :total 个目标中的 :confirmed 个。', 'review_list' => '实践目标回顾', 'status_confirmed' => '浏览器已确认', 'status_skipped' => '未确认', 'status_pending' => '待完成', 'review_note' => '确认仅表示原型匹配到稳定关键点，并不证明正式手语能力。此临时结果不会保存。', 'retry' => '重试此类别', 'choose_other' => '选择其他挑战', 'view_progress' => '查看个人进度',
        'js' => ['load_failed' => '无法加载摄像头识别。请返回或重新加载页面；没有记录分数。', 'start_failed' => '无法访问摄像头。请检查浏览器权限后重试。', 'session_start_failed' => '无法启动计时会话。没有记录分数；请检查网络连接后重试。'],
    ],
    'leaderboard' => ['eyebrow' => '理论排行榜', 'intro' => '字母和数字理论测验分别排名。最高分优先，同分时用时较短者领先。', 'scope_note' => '只有完全相同的理论测验结果会影响此排名。', 'try_practical' => '参加:theme实践', 'mode_theory' => '理论', 'mode_practical' => '摄像头实践'],
    'flash' => ['practical_invalid' => '实践会话无效或已过期，请重新开始。', 'practical_unverified' => '无法验证已发出的实践目标。', 'practical_save_failed' => '无法保存实践分数，请重试。', 'practical_review_expired' => '实践回顾已过期。请开始新尝试以查看另一份回顾。'],
    'signs' => ['category' => ['alphabet' => '字母', 'numbers' => '数字']],
];

$approved = [
    'alphabet' => [
        'A' => ['四指握拢；拇指放在侧面并远离食指指尖。', '握拳，拇指在侧'],
        'B' => ['四指并拢抬起，拇指折向掌心。', '四指并拢'],
        'C' => ['弯曲手指和拇指，留出一个张开的 C 形空间。', '让弯曲的开口清晰可见'],
        'D' => ['抬起食指，其余手指弯向拇指。', '食指向上，底部呈圆形'],
        'E' => ['手指弯向掌心，拇指靠近并置于下方。', '保持紧凑的弯曲形状'],
        'F' => ['食指指尖与拇指相触，其余三指抬起。', '细小的拇指—食指接触'],
        'G' => ['食指和拇指指向侧面，其余手指折叠。', '检查侧向方向'],
        'H' => ['食指和中指并拢向侧面伸出，其余手指折叠。', '两指指向侧面'],
        'I' => ['仅抬起小指，拇指保持折叠。', '仅小指'],
        'K' => ['抬起并分开食指与中指，把拇指放在两指之间。', '拇指位于两根抬起的手指之间'],
        'L' => ['抬起食指并伸出拇指，形成 L。', '清楚的直角'],
        'M' => ['三根手指折在拇指上方，使拇指露在下方。', '拇指位于三指下方'],
        'N' => ['两根手指折在拇指上方，使拇指露在下方。', '拇指位于两指下方'],
        'O' => ['手指弯曲，拇指与食指相触，形成 O。', '拇指接触食指'],
        'P' => ['先做 K 手形，再将其转向下方。', 'K 手形朝下'],
        'Q' => ['先做 G 手形，再将其指向下方。', 'G 手形朝下'],
        'R' => ['抬起食指和中指，并将两指交叉。', '保持两指交叉'],
        'S' => ['手指握拳，拇指横放在拳头前方。', '检查拇指在前方的位置'],
        'T' => ['握拳，并将拇指放在食指与中指之间。', '拇指位于两指之间'],
        'U' => ['食指和中指并拢抬起。', '两指靠拢'],
        'V' => ['食指和中指分开抬起。', '张开 V 形'],
        'W' => ['抬起食指、中指和无名指。', '显示三根手指'],
        'X' => ['抬起食指并将上部关节弯成钩状，其余手指折叠。', '保持食指呈钩状'],
        'Y' => ['抬起小指并伸出拇指。', '拇指与小指'],
    ],
    'numbers' => [
        '0' => ['手指弯曲，拇指与食指相触，形成 0。', '拇指接触食指'], '1' => ['仅抬起食指。', '仅食指'], '2' => ['抬起食指和中指。', '抬起两指'], '3' => ['抬起食指、中指和无名指，拇指不接触小指。', '抬起三指'],
        '4' => ['抬起四指并折叠拇指。', '拇指折叠'], '5' => ['抬起四指并伸出拇指。', '手掌张开'], '6' => ['抬起三指，拇指接触小指。', '拇指接触小指'],
        '7' => ['抬起食指、中指和小指；拇指接触无名指。', '拇指接触无名指'], '8' => ['抬起食指、无名指和小指；拇指接触中指。', '拇指接触中指'], '9' => ['抬起中指、无名指和小指；拇指接触食指。', '拇指接触食指'],
    ],
];
foreach (signCatalog() as $sign) {
    $symbol = $sign['symbol']; $category = $sign['category']; $title = ($category === 'alphabet' ? '字母 ' : '数字 ') . $symbol;
    if (isset($approved[$category][$symbol])) [$description, $tip] = $approved[$category][$symbol];
    elseif ($sign['motion'] === 'dynamic') { $description = $copy['learn']['movement'][$symbol] . '请按 MFD BIM 手语资料库所示动作练习。'; $tip = '动态，仅供学习'; }
    else { $description = '用一只手做出' . $title . '。请在 MFD BIM 手语资料库核对准确方向和手指位置。'; $tip = '比较细微手形'; }
    $copy['signs']['entries'][$category][$symbol] = ['title' => $title, 'description' => $description, 'tip' => $tip];
}
return $copy;
