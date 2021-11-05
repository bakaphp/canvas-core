<?php


use Phinx\Seed\AbstractSeed;

class UsersSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < 20; $i++) {
            $data[] = [
                'uuid' => $faker->uuid,
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'email' => $faker->email,
                'default_company' => 1,
                'user_active' => 1,
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        $this->insert('users', $data);
    }
}