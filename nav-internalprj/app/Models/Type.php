<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Types
 * 
 * @property int $id
 * @property string $type
 * 
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Type extends Model
{
    protected $table = 'types';
	public $timestamps = false;

	protected $fillable = [
		'type'
	];

	public function users()
	{
		return $this->hasMany(User::class, 'id_type');
	}
}
