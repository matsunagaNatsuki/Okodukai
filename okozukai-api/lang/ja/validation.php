<?php

return [

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeには有効なURLを指定してください。',
    'after' => ':attributeには:dateより後の日付を指定してください。',
    'after_or_equal' => ':attributeには:date以降の日付を指定してください。',
    'alpha' => ':attributeにはアルファベットのみ使用できます。',
    'alpha_dash' => ':attributeには英数字、ハイフン、アンダースコアのみ使用できます。',
    'alpha_num' => ':attributeには英数字のみ使用できます。',

    'between' => [
        'array' => ':attributeの項目数は:minから:maxの間で指定してください。',
        'file' => ':attributeは:min KBから:max KBの間で指定してください。',
        'numeric' => ':attributeは:minから:maxの間で指定してください。',
        'string' => ':attributeは:min文字から:max文字の間で指定してください。',
    ],

    'boolean' => ':attributeにはtrueまたはfalseを指定してください。',
    'confirmed' => ':attributeの確認用入力と一致しません。',
    'current_password' => 'パスワードが正しくありません。',

    'date' => ':attributeには有効な日付を指定してください。',
    'date_equals' => ':attributeには:dateと同じ日付を指定してください。',
    'date_format' => ':attributeは:format形式で指定してください。',

    'different' => ':attributeと:otherには異なる値を指定してください。',

    'digits' => ':attributeは:digits桁で指定してください。',
    'digits_between' => ':attributeは:min桁から:max桁の間で指定してください。',

    'email' => ':attributeには有効なメールアドレスを指定してください。',

    'file' => ':attributeにはファイルを指定してください。',
    'filled' => ':attributeには値を指定してください。',

    'image' => ':attributeには画像を指定してください。',

    'integer' => ':attributeには整数を指定してください。',

    'max' => [
        'array' => ':attributeの項目数は:max個以下にしてください。',
        'file' => ':attributeは:max KB以下にしてください。',
        'numeric' => ':attributeは:max以下にしてください。',
        'string' => ':attributeは:max文字以下にしてください。',
    ],

    'min' => [
        'array' => ':attributeの項目数は:min個以上にしてください。',
        'file' => ':attributeは:min KB以上にしてください。',
        'numeric' => ':attributeは:min以上にしてください。',
        'string' => ':attributeは:min文字以上にしてください。',
    ],

    'numeric' => ':attributeには数値を指定してください。',

    'password' => [
        'letters' => ':attributeには少なくとも1文字の英字を含めてください。',
        'mixed' => ':attributeには少なくとも1文字の大文字と小文字を含めてください。',
        'numbers' => ':attributeには少なくとも1文字の数字を含めてください。',
        'symbols' => ':attributeには少なくとも1文字の記号を含めてください。',
        'uncompromised' => '指定された:attributeは情報漏洩した可能性があります。別の:attributeを指定してください。',
    ],

    'regex' => ':attributeの形式が正しくありません。',

    'required' => ':attributeは必須です。',

    'required_if' => ':otherが:valueの場合、:attributeは必須です。',
    'required_with' => ':valuesが指定されている場合、:attributeは必須です。',
    'required_without' => ':valuesが指定されていない場合、:attributeは必須です。',

    'same' => ':attributeと:otherには同じ値を指定してください。',

    'size' => [
        'array' => ':attributeの項目数は:size個にしてください。',
        'file' => ':attributeは:size KBで指定してください。',
        'numeric' => ':attributeは:sizeで指定してください。',
        'string' => ':attributeは:size文字で指定してください。',
    ],

    'string' => ':attributeには文字列を指定してください。',

    'unique' => 'この:attributeはすでに使用されています。',

    'uploaded' => ':attributeのアップロードに失敗しました。',

    'url' => ':attributeには有効なURLを指定してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
        'name' => '名前',
    ],

];
