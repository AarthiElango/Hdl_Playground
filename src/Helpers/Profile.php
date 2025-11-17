<?php 

namespace App\Helpers;
use App\Helpers\DB;

class Profile extends DB{


      public function get_profiles($request, $where=array())
    {

        $uid = $request->getAttribute('user_id');

        $queryParams = $request->getQueryParams();

        // Example: get a specific param 'page', default to 1 if not set
        $page   = isset($queryParams['page']) ? (int) $queryParams['page'] : 1;
        $limit  = isset($queryParams['size']) ? (int) $queryParams['size'] : 10;
        $q      = isset($queryParams['q']) ? $queryParams['q'] : null;
        $offset = ($page - 1) * $limit;

        

        $join = [
            '[>]o_genders(g)'          => ["p.gender_id" => 'id'],
            '[>]o_marital_status(m)'   => ["p.marital_status_id" => 'id'],
            '[>]o_locations(pl)'       => ["p.plocation_id" => 'id'],
            '[>]o_locations(cl)'       => ["p.clocation_id" => 'id'],
            '[>]profile_optionals(po)' => ['p.id' => 'profile_id'],
            '[>]users(u)'               => ['p.created_by'=> 'id']
        ];

        $select = [
            'p.id',
            'p.fullname',
            'p.slug',
            'u.slug(uslug)',
            'age' => $this->db->raw('TIMESTAMPDIFF(YEAR, p.dob, CURDATE())'),
            'g.text(gender)',
            'm.text(maritalStatus)',
            'pl.text(permanent)',
            'cl.text(current)',
            'po.image(image)',

        ];
        
        $total = $this->db->count('profiles(p)',$join,'p.id',$where);

        if ($q) {
            $where["AND #query"] = ['p.fullname[~]' => $q];

        }

        $filtered = $this->db->count('profiles(p)', $join,'p.id', $where);

        $where["ORDER"] = ["p.updated_at" => "DESC"];
        $where["LIMIT"] = [$offset, $limit];

        $profiles = $this->db->select('profiles(p)', $join, $select, $where);

        $s3   = new \App\Helpers\S3;
        foreach ($profiles as &$profile) {
            if (! empty($profile['image'])) {
                $key = 'profiles/'.$profile['uslug'] . "/" . $profile['image'];
                if ($s3->doesObjectExist($key)) {
                    $profile['image'] = $s3->getPresignedUrl($key);
                } else {
                    $profile['image'] = null;
                }

            }

            unset($profile['id']);
            unset($profile['uslug']);
        }

        return compact('profiles', 'filtered', 'total');
    }
}