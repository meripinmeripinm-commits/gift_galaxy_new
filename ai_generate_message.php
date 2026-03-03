<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$mood = $data['mood'] ?? 'love';

$messages = [

'love' => [
"From the moment you entered my life, everything started feeling warmer and more meaningful. This gift is a small reminder of how deeply I care about you and how grateful I am for every moment we share. Your smile has the power to make my worst days better, and I hope this surprise brings you happiness, comfort, and love today and always.",

"Every heartbeat seems to whisper your name, and every thought somehow leads back to you. This gift carries emotions that words can never fully express. I hope it reminds you that you are loved beyond measure and appreciated more than you realize. Thank you for being the beautiful presence that makes my world brighter every single day.",

"This gift may look simple, but it carries countless memories, emotions, and unspoken feelings. You have a special place in my heart that no one else can fill. I hope this small surprise brings a smile to your face and reminds you how deeply valued and loved you truly are, not just today but always.",

"Sometimes words fall short when emotions run deep, but this gift is my way of saying how important you are to me. Your kindness, laughter, and presence make life more beautiful. I hope this surprise wraps you in warmth and reminds you that someone out there is always thinking of you with love.",

"Life feels kinder and more meaningful with you in it. This gift is a token of appreciation for all the joy, comfort, and happiness you bring into my world. I hope it reminds you that you are cherished deeply and that your presence makes everything better, simply by being you.",

"Every shared laugh, every quiet moment, and every memory we create together holds a special place in my heart. This gift is a reflection of the care and affection I feel for you. May it bring you happiness and remind you that you are deeply loved, appreciated, and never alone.",

"Some people change our lives in ways we never expect, and you are one of those people for me. This gift is a small expression of the gratitude and affection I feel toward you. I hope it brings joy, comfort, and a warm smile to your face, reminding you how special you are.",

"Your presence brings light into my life in the most beautiful way. This gift is chosen with love, care, and countless good thoughts. I hope it makes you feel valued and reminds you that you mean more to me than words can ever explain.",

"Through every season and every emotion, your presence remains a constant source of happiness. This gift is my way of sharing a little piece of my heart with you. May it bring comfort, joy, and a reminder that you are truly loved and appreciated.",

"This small surprise carries big emotions. It represents care, affection, and the unspoken bond we share. I hope it brings warmth to your heart and reminds you that someone out there holds you very close in their thoughts and feelings."
],

'surprise' => [
"Surprise! This gift is meant to brighten your day and catch you completely off guard. It was chosen with excitement, care, and a smile. I hope it adds a little magic to your moment and reminds you that unexpected happiness can appear anytime, especially when someone is thinking of you.",

"Not all surprises come with warnings, and this one is no different. This gift is a small reminder that you are appreciated and valued more than you may realize. I hope it makes you smile and turns an ordinary day into a special memory worth remembering.",

"Life feels more exciting with surprises like this. This gift was sent to bring joy, warmth, and a little mystery into your day. I hope it reminds you that someone out there truly enjoys making you happy and creating moments you will always remember.",

"Surprises are special because they come straight from the heart, and this one is no exception. This gift carries good thoughts, positive energy, and a sincere wish to see you smile. May it add brightness and happiness to your day.",

"This gift was planned with a spark of excitement and a lot of care. It’s meant to surprise you in the best way possible and remind you that life has beautiful moments waiting around every corner. I hope this little surprise becomes one of them.",

"Sometimes the best memories are created when we least expect them. This gift is a gentle reminder that you are appreciated and thought of. I hope it brings joy, laughter, and a sense of warmth to your heart today.",

"This surprise comes with no reason other than to make you happy. It was chosen to bring a smile and add a little brightness to your day. I hope it reminds you how special and valued you truly are.",

"Unexpected gifts often carry the sweetest emotions. This one is filled with care, thoughtfulness, and a sincere wish to see you smile. May it make your day better and your heart a little lighter.",

"Surprises don’t need a reason, just a feeling. This gift is meant to bring happiness and remind you that someone is thinking of you with warmth and affection. I hope it makes your day unforgettable.",

"This small surprise holds a big intention: to make you smile. It was sent with excitement, care, and positive thoughts. I hope it adds joy to your day and becomes a happy memory you’ll cherish."
]

];

$list = $messages[$mood] ?? $messages['love'];
$message = $list[array_rand($list)];

echo json_encode(['message' => $message]);
