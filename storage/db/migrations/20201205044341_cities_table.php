<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CitiesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change() : void
    {
        $this->table('countries_cities')
            ->addColumn('states_id', 'integer', ['null' => true])
            ->addColumn('countries_id', 'integer', ['null' => true])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('latitude', 'decimal', ['null' => true])
            ->addColumn('longitude', 'decimal', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('is_deleted', 'integer', ['null' => false, 'default' => '0'])
            ->create();
    }
}
