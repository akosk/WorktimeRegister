<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.12.08.
 * Time: 9:55
 */

namespace app\modules\attendance\models;


use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\web\User;

class UserImportSearch extends UserImport
{


    /**
     * @inheritdoc
     */
//    public function rules()
//    {
//        return [
//            [['username', 'email', 'profile.name'], 'safe'],
//        ];
//    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserImport::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        foreach ($this->attributes as $key => $value) {
            if (strlen($value) > 0) {
                $query->andFilterWhere([
                    'like',
                    $key,
                    $this->$key
                ]);
            }
        }


        return $dataProvider;
    }

}