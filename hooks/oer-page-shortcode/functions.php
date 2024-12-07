<?php
function oer_reviews_data($resource) {
    $data = array();
    $componentRatings = $reviewerComments = '';
    $newRatings = false;

    if ((int) $resource['standardsalignment'] && (int) $resource['standardsalignment'] >= 0) {
        //$componentRatings .= '<li>' . $resource['standardsalignmentcomment'] . ': ' . $resource['standardsalignment'];
        $componentRatings .= '<li>Standards Alignment: ' . $resource['standardsalignment'] . '</li>';
        $newRatings = true;
    }

    if (isset($resource['standardsalignmentcomment']) && strlen($resource['standardsalignmentcomment']) > 0) {
        $reviewerComments .= '<li>Standards Alignment: ' . $resource['standardsalignmentcomment'] . '</li>';
        $newRatings = true;
    }

    if ((int) $resource['subjectmatter'] && (int) $resource['subjectmatter'] >= 0) {
        //$componentRatings .= '<li>' . $resource['subjectmattercomment'] . ': ' . $resource['subjectmatter'];
        $componentRatings .= '<li>Subject Matter: ' . $resource['subjectmatter'] . '</li>';
        $newRatings = true;
    }

    if (isset($resource['subjectmattercomment']) && strlen($resource['subjectmattercomment']) > 0) {
        $reviewerComments .= '<li>Subject Matter: ' . $resource['subjectmattercomment'] . '</li>';
        $newRatings = true;
    }

    if ((int) $resource['supportsteaching'] && (int) $resource['supportsteaching'] >= 0) {
        //$componentRatings .= '<li>' . $resource['supportsteachingcomment'] . ': ' . $resource['supportsteaching'];
        $componentRatings .= '<li>Support Steaching: ' . $resource['supportsteaching'] . '</li>';
        $newRatings = true;
    }

    if (isset($resource['supportsteachingcomment']) && strlen($resource['supportsteachingcomment']) > 0) {
        $reviewerComments .= '<li>Support Steaching: ' . $resource['supportsteachingcomment'] . '</li>';
        $newRatings = true;
    }

    if ((int) $resource['assessmentsquality'] && (int) $resource['assessmentsquality'] >= 0) {
        //$componentRatings .= '<li>' . $resource['assessmentsqualitycomment'] . ': ' . $resource['assessmentsquality'];
        $componentRatings .= '<li>Assessments Quality: ' . $resource['assessmentsquality'] . '</li>';
        $newRatings = true;
    }

    if (isset($resource['assessmentsqualitycomment']) && strlen($resource['assessmentsqualitycomment']) > 0) {
        $reviewerComments .= '<li>Assessments Quality: ' . $resource['assessmentsqualitycomment'] . '</li>';
        $newRatings = true;
    }

    if ((int) $resource['interactivityquality'] && (int) $resource['interactivityquality'] >= 0) {
        //$componentRatings .= '<li>' . $resource['interactivityqualitycomment'] . ': ' . $resource['interactivityquality'];
        $componentRatings .= '<li>Interactivity Quality: ' . $resource['interactivityquality'] . '</li>';
        $newRatings = true;
    }

    if (isset($resource['interactivityqualitycomment']) && strlen($resource['interactivityqualitycomment']) > 0) {
        $reviewerComments .= '<li>Interactivity Quality: ' . $resource['interactivityqualitycomment'] . '</li>';
        $newRatings = true;
    }

    if ((int) $resource['instructionalquality'] && (int) $resource['instructionalquality'] >= 0) {
        //$componentRatings .= '<li>' . $resource['instructionalqualitycomment'] . ': ' . $resource['instructionalquality'];
        $componentRatings .= '<li>Instructional Quality: ' . $resource['instructionalquality'] . '</li>';
        $newRatings = true;
    }

    if (isset($resource['instructionalqualitycomment']) && strlen($resource['instructionalqualitycomment']) > 0) {
        $reviewerComments .= '<li>Instructional Quality: ' . $resource['instructionalqualitycomment'] . '</li>';
        $newRatings = true;
    }

    if ((int) $resource['deeperlearning'] && (int) $resource['deeperlearning'] >= 0) {
        $componentRatings .= '<li>Deeper Learning: ' . $resource['deeperlearning'] . '</li>';
        $newRatings = true;
    }
    if (isset($resource['deeperlearningcomment']) && strlen($resource['deeperlearningcomment']) > 0) {
        $reviewerComments .= '<li>Deeper Learning: ' . $resource['deeperlearningcomment'] . '</li>';
        $newRatings = true;
    }

    if (!$newRatings && ((int) $resource['technicalcompleteness'] || (int) $resource['contentaccuracy'] || (int) $resource['pedagogy'])) {
        $componentRatings = '<li>Technical Completeness: ' . $resource['technicalcompleteness'] . '</li><li>Content Accuracy: ' . $resource['contentaccuracy'] . '</li><li>Appropriate Pedagogy: ' . $resource['pedagogy'] . '</li>';
    }

    if (isset($resource['ratingcomment']) && strlen($resource['ratingcomment']) > 0) {
        $reviewerComments = '<li>' . trim( str_replace('Reviewer Comments:', '', strip_tags($resource['ratingcomment']) ) ) . '</li>';
    }

    $data['componentRatings'] = $componentRatings;
    $data['reviewerComments'] = $reviewerComments;
    $data['newRatings'] = $newRatings;
    return $data;
}
?>