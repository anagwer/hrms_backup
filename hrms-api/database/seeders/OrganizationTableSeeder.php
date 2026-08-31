<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nodes = [
            [
                'text' => 'LIDAH BUAYA GROUP',
                'children' => [
                    [
                        'text' => 'KOPERASI MANDIRI SEJAHTERA',
                        'children' => [
                            ['text' => 'CEO']
                        ]
                    ],
                    [
                        'text' => 'LIDAH BUAYA',
                        'children' => [
                            ['text' => 'CEO']
                        ]
                    ],
                    [
                        'text' => 'LINTAS USAHATAMA TRANS',
                        'children' => [
                            ['text' => 'CEO']
                        ]
                    ],
                    [
                        'text' => 'PALMAS PRIMA INDONESIA',
                        'children' => [
                            ['text' => 'CEO']
                        ]
                    ],
                    [
                        'text' => 'SINAR JOYO BOYO',
                        'children' => [
                            [
                                'text' => 'CEO',
                                'children' => [
                                    [
                                        'text' => 'ICT Division',
                                        'children' => [
                                            [
                                                'text' => 'ICT Head',
                                                'children' => [
                                                    [
                                                        'text' => 'ICT Infrastructure Manager',
                                                        'children' => [
                                                            [
                                                                'text' => 'ICT Network & System Supervisor',
                                                                'children' => [
                                                                    ['text' => 'ICT Network Administrator'],
                                                                    ['text' => 'ICT System Administrator'],
                                                                    ['text' => 'ICT Security Administrator'],
                                                                ]
                                                            ],
                                                            [
                                                                'text' => 'ICT Operation Supervisor',
                                                                'children' => [
                                                                    ['text' => 'ICT Service'],
                                                                    ['text' => 'ICT Asset'],
                                                                ]
                                                            ],
                                                        ]
                                                    ],
                                                    [
                                                        'text' => 'ICT Application Manager',
                                                        'children' => [
                                                            [
                                                                'text' => 'ICT Technical Supervisor',
                                                                'children' => [
                                                                    ['text' => 'Website Developer'],
                                                                    ['text' => 'Mobile Developer'],
                                                                    ['text' => 'AX Developer'],
                                                                ]
                                                            ],
                                                            [
                                                                'text' => 'ICT Functional Supervisor',
                                                                'children' => [
                                                                    ['text' => 'Quality Assurance'],
                                                                    ['text' => 'System Analyst'],
                                                                    ['text' => 'AX Analyst'],
                                                                ]
                                                            ],
                                                        ]
                                                    ],
                                                    [
                                                        'text' => 'ICT Data & AI Manager',
                                                        'children' => [
                                                            [
                                                                'text' => 'ICT Data Supervisor',
                                                                'children' => [
                                                                    ['text' => 'Data Analyst'],
                                                                    ['text' => 'Data Warehouse Specialist'],
                                                                    ['text' => 'Business Analyst'],
                                                                ]
                                                            ],
                                                            [
                                                                'text' => 'ICT AI Supervisor',
                                                                'children' => [
                                                                    ['text' => 'Data Scientist'],
                                                                    ['text' => 'Machine Learning Developer'],
                                                                ]
                                                            ],
                                                        ]
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ],
                    ],
                    [
                        'text' => 'THERMOPAK',
                        'children' => [
                            ['text' => 'CEO']
                        ]
                    ],
                ],
            ],
        ];
        foreach ($nodes as $node) {
            Organization::create($node);
        }
    }
}
