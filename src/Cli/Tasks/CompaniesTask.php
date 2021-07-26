<?php

namespace Canvas\Cli\Tasks;

use Canvas\Models\Companies;
use Canvas\Models\UsersAssociatedApps;
use Canvas\Models\Users;
use Phalcon\Cli\Task as PhTask;

class MainTask extends PhTask
{
    /**
     * Upgrade Kanvas Core version.
     *
     * @return void
     */
    public function syncDefaultCompaniesAction() : void
    {
        $users = Users::find();
        foreach ($users as $user) {
            $associatedApps = UsersAssociatedApps::find([
                'conditions' => "users_id = :users_id: and user_active = 1 and is_deleted = 0",
                'bind' => ["users_id" => $user->getId()]
            ]);

            foreach ($associatedApps as $associatedApp) {
                echo("\n Setting Default companies and branches for user with id: {$user->getId()}");
                if ($user->get(Companies::DEFAULT_COMPANY_APP . $associatedApp->apps_id)) {
                    $user->set(Companies::DEFAULT_COMPANY_APP . $associatedApp->apps_id, $associatedApp->company->getId());
                }
                if ($user->get(Companies::DEFAULT_COMPANY_BRANCH_APP . $associatedApp->apps_id . '_' . $associatedApp->company->getId())) {
                    $user->set(Companies::DEFAULT_COMPANY_BRANCH_APP . $associatedApp->apps_id . '_' . $associatedApp->company->getId(), $associatedApp->company->branch->getId());
                }
            }
        }
    }
}
