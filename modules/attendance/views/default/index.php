<?php
$this->title = Yii::t('attendance', 'Jelenléti ív');
$this->params['breadcrumbs'][] = $this->title;

?>
<div ng-app="attendance" ng-view>
</div>

<script type="text/html" id="attendance-template">
    <div class="row">
        <div class="col-md-8">
            <h2><?= $user->profile->name ?> (<?= $user->username ?>) jelenléti íve</h2>

        </div>
        <div class="col-md-4">
            <h2>
                <div class="pull-right"><a class="btn btn-success" ng-href="{{getPreviousMonthUrl()}}"><i class="fa
    fa-angle-left"></i>
                        Előző hónap</a>
                    <a class="btn btn-success" ng-href="{{getNextMonthUrl()}}">Következő hónap <i class="fa
    fa-angle-right"></i></a>
                </div>
            </h2>
        </div>
    </div>
    <br/>

    <div class="row">
        <div ng-cloak class="col-md-8">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Jelenléti ív
                </div>
                <div class="panel-body">

                    <div ng-show="isBusy" class="text-center"><i class="fa fa-cog fa-spin fa-3x"></i></div>
                    <table class="table" ng-show="!isBusy">
                        <thead>
                        <tr>
                            <th>{{year}}. {{helpers.getMonthName(month)}}</th>
                            <th>Érkezés</th>
                            <th>Távozás</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>

                        <div>
                            <tr ng-class="getRowBg(a)" ng-repeat="a in ourData.attendances" focus-time>
                                <td style="max-width:50px">
                                    <span
                                        ng-class="helpers.isCurrentDay(a.date) ? 'bold':''">{{a.date | justDay}}</span>
                                </td>

                                <td ng-show="a.userWorkDay">
                                    <input type="text"
                                           style="max-width:120px"
                                           ng-focus="setFocusedItem(a)"
                                           ng-model="a.from" empty-to-null

                                           placeholder="Érkezés ideje"
                                           class="form-control"
                                           ng-class="helpers.isCurrentDay(a.date) && a.from===null?'focused':''"></td>
                                <td ng-show="a.userWorkDay">
                                    <input type="text" class="form-control" style="max-width:120px"
                                           ng-model="a.to" empty-to-null

                                           placeholder="Távozás ideje"
                                           ng-class="helpers.isCurrentDay(a.date) && a.from!==null && a
        .to===null?'focused':''"></td>

                                <td ng-show="!a.userWorkDay" colspan="2"><span
                                        class="text-danger">{{offDayText(a)}}</span></td>

                                <td style="min-width:40px">
                                    <div ng-show="focusedItem==a && isSave" class="text-center">
                                        <i class="fa fa-cog fa-spin fa-2x"></i>
                                    </div>
                                </td>

                                <td>
                                    <div class="btn-group" ng-show="a.userWorkDay">
                                        <button type="button" class="btn btn-success btn-xs dropdown-toggle"
                                                data-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-calendar fa-2x"></i>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li ng-repeat="absenceType in ourData.absenceTypes">
                                                <a ng-click="setAbsence(a,absenceType)">
                                                    {{absenceType.label}}
                                                </a>
                                            </li>

                                        </ul>
                                    </div>
                                    <span ng-show="isAdmin && a.workDay && helpers.isAfterCurrentMonth(a.date)"
                                          ng-click="setRedLetterDay(a)"
                                          class="btn btn-success btn-xs"
                                          style="font-size:17px"
                                          href="#" title="Ünnepnap">
                                            <strong>Ü</strong>
                                    </span>
                                    <span ng-show="isAdmin && !a.workDay && helpers.isAfterCurrentMonth(a.date)"
                                          ng-click="setWorkingDay(a)"
                                          class="btn btn-success btn-xs"
                                          style="font-size:17px"
                                          href="#" title="Munkanap">

                                        <strong>M</strong>
                                    </span>


                                    <span ng-show="(!isUserOnFreeDay(a) && ((a.from!==null) || (a.to!==null))) ||
                                    a.userAbsence!==undefined"
                                          ng-click="clearTimes(a)"
                                          class="btn btn-danger btn-xs"
                                          href="#" title="Töröl">
                                            <i class="fa fa-remove fa-2x"></i>
                                    </span>

                                </td>
                            </tr>
                        </div>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Jelmagyarázat
                </div>
                <div class="panel-body">

                    <div class="row">
                        <div class="col-md-2">
                            <span type="button" class="btn btn-success btn-xs dropdown-toggle"
                                  data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-calendar fa-2x"></i>
                            </span>
                        </div>
                        <div class="col-md-10">Távollét</div>
                    </div>
                    <br/>


                    <div class="row">

                        <div class="col-md-2"><span
                                class="btn btn-success btn-xs"
                                style="font-size:17px"
                                href="#" title="Ünnepnap">
                                <strong>Ü</strong>
                            </span></div>
                        <div class="col-md-10">Ünnepnapra állítás</div>
                    </div>
                    <br/>

                    <div class="row">
                        <div class="col-md-2"><span
                                class="btn btn-success btn-xs"
                                style="font-size:17px"
                                href="#" title="Munkanap">
                                <strong>M</strong>
                            </span></div>
                        <div class="col-md-10">Munkanapra állítás</div>
                    </div>
                    <br/>

                    <div class="row">

                        <div class="col-md-2"><span class="btn btn-danger btn-xs" href="#" title="Töröl"><i class="fa fa-remove
                            fa-2x"></i></span>
                        </div>
                        <div class="col-md-10">
                            Adatok törlése
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</script>

<script>
    var BASE_URL = "<?=\Yii::$app->homeUrl ?>";
    var isAdmin = <?=\Yii::$app->user->can('admin') ? 'true':'false' ?>;
</script>