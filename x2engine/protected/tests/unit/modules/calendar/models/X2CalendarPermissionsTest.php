<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

Yii::import ('application.modules.users.models.*');

class X2CalendarPermissionsTest extends X2DbTestCase {

    public $fixtures = array (
        'calendarPermissions' => 'X2Calendar',
        'users' => 'User',
    );

    /**
     * Ensure that list of viewable calendars correctly reflects calendar permissions records
     */
    public function testGetViewableUserCalendarNames () {
        TestingAuxLib::suLogin ('admin');        
        $viewable = array_keys (X2CalendarPermissions::getViewableUserCalendarNames ());
        $this->assertEquals (array_merge (
            array ('Anyone'), Yii::app()->db->createCommand ("
                SELECT username
                FROM x2_users
            ")->queryColumn ()), 
            ArrayUtil::sort ($viewable));

        $user = $this->users ('testUser');
        TestingAuxLib::suLogin ('testUser');        
        $viewable = array_keys (X2CalendarPermissions::getViewableUserCalendarNames ());
        $grantedUsers = array_unique (array_merge (
            array ('Anyone', 'testuser'), Yii::app()->db->createCommand ("
                /**
                 * get names of users who have granted view permission to testuser and names of
                 * users who have not set up calendar permissions
                 */
                SELECT distinct(username)
                FROM x2_users as t, x2_calendar_permissions
                WHERE other_user_id=:userId OR t.id NOT in (
                    SELECT distinct(user_id)
                    FROM x2_calendar_permissions
                )
            ")->queryColumn (array (':userId' => $user->id))));
        $this->assertEquals (ArrayUtil::sort ($grantedUsers), 
            ArrayUtil::sort ($viewable));
    }

}

?>
