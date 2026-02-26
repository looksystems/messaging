<?php

namespace Tests\Unit\Codegen;

use Look\Messaging\Codegen\DtoBuilder;

test('it can generate code', function () {

    $json = file_get_contents(
        $this->resources('schemas/shop/events/order-completed.json')
    );
    $schema = json_decode($json, false);
    $builder = DtoBuilder::fromSchema($schema);

    // cast to string for correct snapshot matching
    $output = $builder->output();
    foreach ($output as &$part) {
        $part['code'] = (string) $part['code'];
    }

    expect($output)->toMatchSnapshot();

});
