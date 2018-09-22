# a simple query builder

$User = db::table("users")->selectOne("id")->where(["name"=>"Ahmet"])->end();

$Users = db::table("users")->select()->where("id>:id",["id"=>30])->end();

$UpdateResult = db::table("users")->update(["name"=>"Mehmet"])->where(["id"=>19])->end();

$DeleteResult = db::table("users")->delete()->where(["id"=>19])->end();

$InsertResult = db::table("users")->insert(["id"=>19,"name"=>"Ahmet","surname"=>"Kara"])->end();
