<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Level
 * 
 * @property int $id
 * @property string $level
 * 
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Level extends Model
{
	protected $table = 'level';
	public $timestamps = false;

	protected $fillable = [
		'level'
	];

	public function users()
	{
		return $this->hasMany(User::class, 'id_level');
	}
}
