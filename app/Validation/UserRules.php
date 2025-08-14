<?php
namespace App\Validation;
use App\Models\TeamModel;

class UserRules
{

  public function validateUser(string $str, string $fields, array $data){
    $model = new TeamModel();
    $user = $model->where('email', $data['email'])
                  ->first();

    if(!$user)
      return false;

    return password_verify($data['password'], $user['password']);
  }

  public function verifyStatus(string $str){
    $model = new TeamModel();
    $user = $model->where('email', $str)
                  ->where('is-active', 1)
                  ->first();

    if(!$user)
      return false;
  }

  // public function validatePassCode(string $str){
  //   $pass_code = getenv('PASS_CODE');
  //   if($str == $pass_code){
  //     return true;
  //   }else{
  //     return false;
  //   }
  // }

}
