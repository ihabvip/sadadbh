<?php

return [
  'url' => [
      'test' => 'https://eps-net-uat.sadadbh.com/',
      'live' => 'https://eps-net.sadadbh.com/',
  ],
    'apiKey' => env('SADAD_API_KEY',null),
    'branchId' => env('SADAD_BRANCH_ID',null),
    'vendorId' => env('SADAD_VENDOR_ID',null),
    'terminalId' => env('SADAD_TERMINAL_ID',null),
    'successUrl' => env('SADAD_SUCCESS_URL',null),
    'errorUrl' => env('SADAD_ERROR_URL',null),
];
