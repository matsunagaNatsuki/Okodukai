<?php

test('the application redirects to parent login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/parent/login');
});
