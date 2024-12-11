<?php

namespace APP\plugins\generic\scieloTranslationsFields\classes;

use PKP\db\DAO;
use Illuminate\Support\Facades\DB;
use PKP\security\Role;

class TranslationsFieldsDAO extends DAO
{
    public function getSubmitterId($submissionId)
    {
        $result = DB::table('stage_assignments AS sa')
            ->leftJoin('user_groups AS ug', 'sa.user_group_id', '=', 'ug.user_group_id')
            ->where('sa.submission_id', $submissionId)
            ->where('ug.role_id', Role::ROLE_ID_AUTHOR)
            ->select('sa.user_id')
            ->first();

        if (!$result) {
            return null;
        }

        return get_object_vars($result)['user_id'];
    }
}
