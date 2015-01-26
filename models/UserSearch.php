<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.06.
 * Time: 10:25
 */

namespace app\models;

use dektrium\user\models\UserSearch as BaseUserSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class UserSearch extends BaseUserSearch
{

    public $name;

    public function rules()
    {
        return [
            [['created_at'], 'integer'],
            [['name', 'username', 'email', 'registered_from'], 'safe'],
        ];
    }


    public function search($params)
    {
        $query = $this->module->manager->createUserQuery();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->joinWith(['profile']);
        $query->andFilterWhere([
            'LIKE',
            'profile.name',
            $this->name
        ]);

        $this->addCondition($query, 'username', true);
        $this->addCondition($query, 'email', true);
        $this->addCondition($query, 'created_at');
        $this->addCondition($query, 'registered_from');

        $dataProvider->sort->attributes['name'] = [
            'asc'  => ['profile.name' => SORT_ASC],
            'desc' => ['profile.name' => SORT_DESC],
        ];

        return $dataProvider;
    }

}