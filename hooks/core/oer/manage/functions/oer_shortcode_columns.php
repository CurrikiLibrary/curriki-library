<?php
function getOerColumns() : array {
    $columns = [
        'resourceid',
        'licenseid',
        'contributorid',
        'contributiondate',
        'description',
        'title',
        'keywords',
        'generatedkeywords',
        'language',
        'lasteditorid',
        'lasteditdate',
        'currikilicense',
        'externalurl',
        'resourcechecked',
        'content',
        'resourcecheckrequestnote',
        'resourcecheckdate',
        'resourcecheckid',
        'resourcechecknote',
        'studentfacing',
        'source',
        'reviewstatus',
        'lastreviewdate',
        'reviewedbyid',
        'reviewrating',
        'technicalcompleteness',
        'contentaccuracy',
        'pedagogy',
        'ratingcomment',
        'standardsalignment',
        'standardsalignmentcomment',
        'subjectmatter',
        'subjectmattercomment',
        'supportsteaching',
        'supportsteachingcomment',
        'assessmentsquality',
        'assessmentsqualitycomment',
        'interactivityquality',
        'interactivityqualitycomment',
        'instructionalquality',
        'instructionalqualitycomment',
        'deeperlearning',
        'deeperlearningcomment',
        'partner',
        'createdate',
        'type',
        'featured',
        'page',
        'active',
        'public',
        'xwd_id',
        'mediatype',
        'access',
        'memberrating',
        'aligned',
        'pageurl',
        'indexed',
        'lastindexdate',
        'indexrequired',
        'indexrequireddate',
        'rescrape',
        'gobutton',
        'downloadbutton',
        'topofsearch',
        'remove',
        'spam',
        'topofsearchint',
        'partnerint',
        'reviewresource',
        'oldurl',
        'contentdisplayok',
        'metadata',
        'approvalStatus',
        'approvalStatusDate',
        'spamUser'
    ];
    
    return $columns;
}

function getOerMetadata(): array {
    $metadata = [
        'educationlevels',
        'subjectareas'
    ];
    return $metadata;
}

function getOerEducationlevels() : array {
    $education_levels = array(
        array('title' => __('Preschool (Ages 0-4)', 'curriki'), 'levels' => '8|9', 'arlevels' => array(8, 9)),
        array('title' => __('Kindergarten-Grade 2 (Ages 5-7) ', 'curriki'), 'levels' => '3|4', 'arlevels' => array(3, 4)),
        array('title' => __('Grades 3-5 (Ages 8-10)', 'curriki'), 'levels' => '5|6|7', 'arlevels' => array(5, 6, 7)),
        array('title' => __('Grades 6-8 (Ages 11-13)', 'curriki'), 'levels' => '11|12|13', 'arlevels' => array(11, 12, 13)),
        array('title' => __('Grades 9-10 (Ages 14-16)', 'curriki'), 'levels' => '15|16', 'arlevels' => array(15, 16)),
        array('title' => __('Grades 11-12 (Ages 16-18)', 'curriki'), 'levels' => '17|18', 'arlevels' => array(17, 18)),
        array('title' => __('College & Beyond', 'curriki'), 'levels' => '23|24|25', 'arlevels' => array(23, 24, 25)),
        array('title' => __('Professional Development', 'curriki'), 'levels' => '19|20', 'arlevels' => array(19, 20)),
        array('title' => __('Special Education', 'curriki'), 'levels' => '26|21', 'arlevels' => array(26, 21)),
    );
    return $education_levels;
}
?>