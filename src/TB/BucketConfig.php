<?php

return [
  'tiku' => [
    'yunchao' => [
      'time'   => '3',
      'num'    => '100',
      'grade'  => '2',
      'tactic' => [
        0 => 'IP',
      ],
      'controller' => [
        0 => 'test',
        1 => 'def',
      ],
      'test' => [
        'time'   => '1',
        'num'    => '1',
        'grade'  => '2',
        'tactic' => [
          0 => 'USER',
          1 => 'IP',
        ],
        'action' => [
          0 => 'get',
          1 => 'put',
          2 => 'post',
          3 => 'del',
        ],
        'get' => [
          'time'   => '10',
          'num'    => '1',
          'grade'  => '2',
          'tactic' => [
            0 => 'USER',
          ],
        ],
        'put' => [
          'time'   => '10',
          'num'    => '12',
          'grade'  => '1',
          'tactic' => [
            0 => 'USER',
          ],
        ],
        'post' => [
          'time'   => '12',
          'num'    => '111',
          'grade'  => '1',
          'tactic' => [
            0 => 'IP',
          ],
        ],
        'del' => [
          'time'   => '1',
          'num'    => '123',
          'grade'  => '1',
          'tactic' => [
            0 => 'USER',
            1 => 'IP',
          ],
        ],
      ],
      'def' => [
        'time'   => '4',
        'num'    => '20',
        'grade'  => '1',
        'tactic' => [
          0 => 'USER',
        ],
      ],
    ],
    'def' => [
      'time'   => '2',
      'num'    => '1',
      'grade'  => '1',
      'tactic' => [
        0 => 'IP',
      ],
    ],
  ],
  'yunchao' => [
    'time'   => '10',
    'num'    => '11',
    'grade'  => '1',
    'tactic' => [
      0 => 'IP',
      1 => 'URL',
    ],
  ],
  'app1' => [
    'time'   => '12',
    'num'    => '1',
    'grade'  => '2',
    'tactic' => [
      0 => 'IP',
    ],
    'app2' => [
      'time'   => '10',
      'num'    => '5',
      'grade'  => '2',
      'tactic' => [
        0 => 'USER',
        1 => 'IP',
      ],
      'controller' => [
        0 => 'test',
      ],
      'test' => [
        'time'   => '5',
        'num'    => '10',
        'grade'  => '1',
        'tactic' => [
          0 => 'IP',
        ],
      ],
    ],
  ],
];
