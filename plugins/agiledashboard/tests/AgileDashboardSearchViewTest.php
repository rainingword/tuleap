<?php
/**
 * Copyright (c) Enalean, 2012. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) .'/../include/AgileDashboard/SearchView.class.php';

Mock::generate('Service');
Mock::generate('Project');
Mock::generate('Tracker_Report');
Mock::generate('Tracker_ArtifactFactory');
Mock::generate('Tracker_Artifact');
Mock::generate('Tracker');
Mock::generate('Tracker_FormElement_Field_List');
Mock::generate('Tracker_SharedFormElementFactory');
Mock::generate('Tracker_Artifact_Changeset');

class AgileDashboardViewTest extends TuleapTestCase {
    
    function testRenderShouldDisplayServiceHeaderAndFooter() {
        $service = new MockService();
        $service->expectOnce('displayHeader');
        $service->expectOnce('displayFooter');
        $criteria = array();
        
        $view = $this->GivenASearchView($service, $criteria, array());
        
        ob_start();
        $view->render();
        ob_end_clean();
    }
    
    function testRenderShouldDisplayArtifacts() {
        $service = new MockService();
        $criteria = array();
        $artifacts = array(
            array(
                'id' => '6',
                'title' => 'As a user I want to search on shared fields',
            ),
            array(
                'id' => '8',
                'title' => 'Add the form',
            )
        );
        
        $view = $this->GivenASearchView($service, $criteria, $artifacts);
        
        ob_start();
        $view->render();
        $output = ob_get_clean();
        
        $this->assertPattern('/As a user I want to search on shared fields/', $output);
        $this->assertPattern('/Add the form/', $output);
    }
    
    function testRenderShouldDisplaySharedFieldValue() {
        $service = new MockService();
        $criterion = new stdClass();
        $criterion->field = new MockTracker_FormElement_Field_List();
        $criterion->field->setReturnValue('fetchChangesetValue', 'shared field value', array('6', '12345', null));
        $criteria = array($criterion);
        
        $artifacts = array(
            array(
                'id' => '6',
                'title' => 'As a user I want to search on shared fields',
            )
        );
        $view = $this->GivenASearchView($service, $criteria, $artifacts);
        
        
        ob_start();
        $view->render();
        $output = ob_get_clean();
        
        $this->assertPattern('/As a user I want to search on shared fields/', $output);
        $this->assertPattern('/shared field value/', $output);
    }
    
    private function GivenASearchView($service, $criteria, $artifacts) {
        $report = new MockTracker_Report();
        $artifact_factory = $this->GivenAnArtifactFactory($artifacts);
        $shared_factory = $this->GivenASharedFactory($criteria);
        $view = new AgileDashboard_SearchView($service, $GLOBALS['Language'], $report, $criteria, $artifacts, $artifact_factory, $shared_factory);
        return $view;
    }
    
    private function GivenASharedFactory($criteria) {
        $shared_factory = new MockTracker_SharedFormElementFactory();
        foreach ($criteria as $criterion) {
            $shared_factory->setReturnValue('getGoodField', $criterion->field, array('*', $criterion->field));
        }
        return $shared_factory;
    }
    
    private function GivenAnArtifactFactory($artifacts) {
        $factory = new MockTracker_ArtifactFactory();
        foreach ($artifacts as $row) {
            $artifact = $this->GivenAnArtifact($row['id']);
            $factory->setReturnValue('getArtifactById', $artifact, array($row['id']));
        }
        return $factory;
    }
    
    private function GivenAnArtifact($id) {
        $changeset = $this->GivenALastChangeset();
        $artifact = new MockTracker_Artifact();
        $artifact->expectOnce('fetchDirectLinkToArtifact');
        $artifact->setReturnValue('getLastChangeset', $changeset);
        $artifact->setReturnValue('getId', $id);
        return $artifact;
    }
    
    private function GivenALastChangeset() {
        $changeset = new MockTracker_Artifact_Changeset();
        $changeset->setReturnValue('getId', '12345');
        return $changeset;
    }
}
?>
