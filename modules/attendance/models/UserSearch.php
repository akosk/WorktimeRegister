<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.11.28.
 * Time: 13:13
 */


namespace app\modules\attendance\models;

use app\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * CompletionSearch represents the model behind the search form about `app\modules\attendance\models\Completion`.
 */
class UserSearch extends \app\models\User
{

    public $year;
    public $month;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email', 'profile.name','profile.taxnumber','profile.department.name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['profile.name', 'profile.taxnumber','profile.department.name']);
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
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->joinWith(['profile']);
        $query->joinWith(['currentCompletions']);
        $query->joinWith(['profile.department']);

        User::$yearFilter = $this->year;
        User::$monthFilter = $this->month;

        if (!(Yii::$app->user->can('admin') || Yii::$app->user->can('payroll_manager'))) {
            $currentUser = User::findOne(\Yii::$app->user->id);
            $query->andFilterWhere([
                '=',
                'profile.department_id',
                $currentUser->profile->department_id
            ]);
        }

        $dataProvider->sort->attributes['profile.name'] = [
            'asc'  => ['profile.name' => SORT_ASC],
            'desc' => ['profile.name' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['currentCompletions.id'] = [
            'asc'  => ['completion.id' => SORT_ASC],
            'desc' => ['completion.id' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['profile.taxnumber'] = [
            'asc'  => ['profile.taxnumber' => SORT_ASC],
            'desc' => ['profile.taxnumber' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['profile.department.name'] = [
            'asc'  => ['department.name' => SORT_ASC],
            'desc' => ['department.name' => SORT_DESC],
        ];


        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }


        $query->andFilterWhere([
            'like',
            'username',
            $this->username
        ]);
        $query->andFilterWhere([
            'like',
            'profile.name',
            $this->attributes["profile.name"],
            $this->attributes["currentCompletions.id"],
        ]);
        $query->andFilterWhere([
            'like',
            'profile.taxnumber',
            $this->attributes["profile.taxnumber"],
            $this->attributes["currentCompletions.id"],
        ]);
        $query->andFilterWhere([
            'like',
            'department.name',
            $this->attributes['profile.department.name'],
            $this->attributes["currentCompletions.id"],
        ]);

        return $dataProvider;
    }

}