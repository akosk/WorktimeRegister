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

    <div class="panel panel-primary" ng-show="isInstructor">
        <div class="panel-heading">
            Jelenléti ív igazolása oktatók számára
        </div>
        <div class="panel-body">
            <div class="checkbox">
                <label>
                    <input ng-disabled="editDisabled" type="checkbox" ng-model="instructorAttendance"> {{year}}.
                    {{helpers
                    .getMonthName
                    (month)}}
                    hónapban a
                    jelenléti
                    kötelezettségeimnek eleget tettem.
                </label>
            </div>
        </div>
    </div>

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
                    <th><span ng-show="!isInstructor">Érkezés</span></th>
                    <th><span ng-show="!isInstructor">Távozás</span></th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                <tr ng-class="getRowBg(a)" ng-repeat="a in ourData.attendances" focus-time>
                    <td style="max-width:50px">
                                    <span
                                        ng-class="helpers.isCurrentDay(a.date) ? 'bold':''">{{a.date | justDay}}</span>
                    </td>


                    <td ng-show="a.userWorkDay && !isInstructor">
                        <input type="text"
                               class="form-control"
                               style="max-width:120px"
                               ng-model="a.from"
                               empty-to-null
                               ak-time-mask
                               ng-focus="setFocusedItem(a)"
                               ng-blur="unsetFocusedItem(a)"
                               placeholder="Érkezés ideje"
                               ng-disabled="ourData.isAttendancesClosed() || editDisabled"
                               ng-class="helpers.isCurrentDay(a.date)
                                             && a.from===null
                                             ? 'focused' : '' ">
                    </td>
                    <td ng-show="a.userWorkDay && !isInstructor">
                        <input type="text"
                               class="form-control"
                               style="max-width:120px"
                               ng-model="a.to"
                               empty-to-null
                               ak-time-mask
                               ng-focus="setFocusedItem(a)"
                               ng-blur="unsetFocusedItem(a)"
                               placeholder="Távozás ideje"
                               ng-disabled="ourData.isAttendancesClosed() || editDisabled"
                               ng-class="helpers.isCurrentDay(a.date)
                                             && a.from!==null
                                             && a.to===null
                                             ? 'focused' : ''">

                    </td>

                    <td ng-show="!a.userWorkDay || isInstructor" colspan="2"><span
                            class="text-danger">{{offDayText(a)}}</span></td>

                    <td style="min-width:40px">
                        <div ng-show="focusedItem==a && isSave" class="text-center">
                            <i class="fa fa-cog fa-spin fa-2x"></i>
                        </div>
                    </td>


                    <!-- Actions  -->

                    <td>
                        <div class="btn-group"
                             ng-show="a.userWorkDay || (a.userWorkDay===false && a.userAbsenceCode!==undefined)">
                            <button ng-show="(!ourData.isAbsencesClosed() || currentDateIsAfterAbsenceClose(a)) &&
                            !editDisabled && !ourData.isAttendancesClosed()" type="button"
                                    class="btn
                                btn-success btn-xs dropdown-toggle"
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

                                    <span
                                        ng-show="
                                        a.workDay===false
                                        && a.userWorkDay===false
                                        && a.userAbsence===undefined
                                        && !isInstructor"


                                        ng-click="setCustomWorkingDay(a, true)"
                                        class="btn btn-success btn-xs"
                                        style="font-size:17px"
                                        href="#" title="Dolgozom ezen a napon">
                                            Dolgozom
                                    </span>
                                    <span
                                        ng-show="
                                        a.workDay===false
                                        &&
                                        (a.userWorkDay===true || (a.userWorkDay===false && a.userAbsence!==undefined))
                                        && !isInstructor"
                                        ng-click="setCustomWorkingDay(a, false)"
                                        class="btn btn-success btn-xs"
                                        style="font-size:17px"
                                        href="#" title="Mégsem dolgozom ezen a napon">
                                            Mégsem dolgozom
                                    </span>

                                    <span ng-show="((!isUserOnFreeDay(a) && ((a.from!==null) || (a.to!==null))) ||
                                    (a.userAbsence!==undefined && (!ourData.isAbsencesClosed() ||
                                    currentDateIsAfterAbsenceClose(a)) )
                                    )
                                     && !ourData
                                    .isAttendancesClosed() && !editDisabled"
                                          ng-click="clearTimes(a)"
                                          class="btn btn-danger btn-xs"
                                          href="#" title="Töröl">
                                            <i class="fa fa-remove fa-2x"></i>
                                    </span>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="col-md-4">

    <div class="panel panel-primary">
        <div class="panel-heading">
            Funkciók
        </div>
        <div class="panel-body">
            <a ng-href="{{getReportUrl('report-attendance')}}"
               class="btn btn-success" style="margin-bottom: 5px"><i
                    class="fa fa-file-pdf-o"></i> Jelenlét jelentés (PDF generálás) </a>


        </div>
    </div>

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


            <div class="row" style="margin-bottom:15px" ng-show="isAdmin">

                <div class="col-md-2"><span
                        class="btn btn-success btn-xs"
                        style="font-size:17px"
                        href="#" title="Ünnepnap">
                                <strong>Ü</strong>
                            </span></div>
                <div class="col-md-10">Ünnepnapra állítás</div>
                <br/>
            </div>


            <div class="row" style="margin-bottom:15px" ng-show="isAdmin">
                <div class="col-md-2"><span
                        class="btn btn-success btn-xs"
                        style="font-size:17px"
                        href="#" title="Munkanap">
                                <strong>M</strong>
                            </span></div>
                <div class="col-md-10">Munkanapra állítás</div>
                <br/>
            </div>


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

    var BASE_URL = "<?=\Yii::$app->homeUrl ?>/";
    var isAdmin = <?=\Yii::$app->user->can('admin') ? 'true':'false' ?>;
    var isInstructor = <?= $userRoles['instructor'] ? 'true':'false' ?>;
    var isDepLeader = <?=\Yii::$app->user->can('dep_leader') ? 'true':'false' ?>;
    var isDepAdmin = <?=\Yii::$app->user->can('dep_admin') ? 'true':'false' ?>;
    var isPayrollManager = <?=\Yii::$app->user->can('payroll_manager') ? 'true':'false' ?>;
    var userId =<?= $user->id?>;

    // ha admin vagy ha szervezeti egység vezető/admin és ugyanaz a egység vagy a saját honlapodat szerkeszted
    var editDisabled =
        <?=
        !(\Yii::$app->user->can('admin') ||
        (
            (\Yii::$app->user->can('dep_admin') || \Yii::$app->user->can('dep_leader')) &&
        $currentUser->profile->department_id==$user->profile->department->id) ||
        $user->id==Yii::$app->user->id)
        ? 'true':'false' ?>;
</script>