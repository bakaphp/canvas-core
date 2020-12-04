<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddressUsers extends AbstractMigration
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
        $this->table('users')
            ->addColumn('address_1 ', 'text', ['null' => true])
            ->addColumn('address_2', 'text', ['null' => true])
            ->addColumn('zip_code', 'text', ['null' => true])
            ->save();
    }
}
