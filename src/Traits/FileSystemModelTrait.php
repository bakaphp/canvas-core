<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Baka\Database\Model;
use Canvas\Models\SystemModules;
use Canvas\Models\FileSystem;

/**
 * Trait ResponseTrait.
 *
 * @package Canvas\Traits
 *
 * @property Users $user
 * @property AppsPlans $appPlan
 * @property CompanyBranches $branches
 * @property Companies $company
 * @property UserCompanyApps $app
 * @property \Phalcon\Di $di
 *
 */
trait FileSystemModelTrait
{
    /**
     * Delete all the files from a module
     *
     * @return void
     */
    public function deleteAllFiles(): bool
    {
        $systemModule = SystemModules::getSystemModuleByModelName(self::class);

        $files = FileSystem::getAllByEntityId($this->getId(), $systemModule);
        $files->delete();

        return true;
    }
}
