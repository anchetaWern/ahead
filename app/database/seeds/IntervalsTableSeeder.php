<?php
class IntervalsTableSeeder extends Seeder {

    public function run()
    {
        DB::table('intervals')->truncate();
        DB::table('intervals')->insert(
            array(
                array(
                    'name' => 'every 1 hour',
                    'rule' => 'add',
                    'hours' => 1
                ),
                array(
                    'name' => 'every 2 hours',
                    'rule' => 'add',
                    'hours' => 2
                ),
                array(
                    'name' => 'every 3 hours',
                    'rule' => 'add',
                    'hours' => 3
                ),
                array(
                    'name' => 'every 4 hours',
                    'rule' => 'add',
                    'hours' => 4
                ),
                array(
                    'name' => 'every 5 hours',
                    'rule' => 'add',
                    'hours' => 5
                ),
                array(
                    'name' => 'random time in the next 72 hours',
                    'rule' => 'random',
                    'hours' => 72
                ),
                array(
                    'name' => 'random time in the next 168 hours',
                    'rule' => 'random',
                    'hours' => 168
                )
            )
        );
    }

}