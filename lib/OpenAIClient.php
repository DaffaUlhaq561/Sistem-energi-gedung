<?php

function ai_openai_request($apiKey, $model, $temperature, $systemPrompt, $messages)
{
    $payload = [
        'model' => $model,
        'messages' => array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            array_map(function ($m) {
                return [
                    'role' => $m['role'],
                    'content' => $m['content'],
                ];
            }, $messages)
        ),
        'temperature' => $temperature,
        'max_tokens' => 400,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
    ]);

    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception('Gagal menghubungi OpenAI: ' . $err);
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code < 200 || $code >= 300) {
        throw new Exception('OpenAI HTTP ' . $code . ': ' . $resp);
    }

    $data = json_decode($resp, true);
    if (!$data) {
        throw new Exception('Respons OpenAI tidak valid');
    }

    $text = null;
    if (isset($data['choices'][0]['message']['content'])) {
        $text = $data['choices'][0]['message']['content'];
    }

    if ($text === null) {
        throw new Exception('Tidak menemukan konten jawaban OpenAI');
    }

    return trim($text);
}
