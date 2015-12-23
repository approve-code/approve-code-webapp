<?php

namespace ApproveCode\Bundle\WebhookBundle\Helper;

class StatusMarkerHelper
{
    const APPROVE_TYPE = 'status_marker_approve';
    const UNDER_REVIEW_TYPE = 'status_marker_under_review';
    const REJECT_TYPE = 'status_marker_reject';

    /**
     * Try to find status marker
     *
     * @param string $comment
     * @return null|string
     */
    public function getStatusMarker($comment)
    {
        $statusMarker = null;

        $applicableMarkers = array_merge(
            $this->getApproveMarkers(),
            $this->getUnderReviewMarkers(),
            $this->getRejectMarkers()
        );

        foreach ($applicableMarkers as $applicableMarker) {
            if (false !== strpos($comment, $applicableMarker)) {
                $statusMarker = $applicableMarker;
                break;
            }
        }

        return $statusMarker;
    }

    /**
     * Get type of marker
     *
     * @param $marker
     * @return null|string
     */
    public function getMarkerType($marker)
    {
        if (in_array($marker, $this->getApproveMarkers(), true)) {
            return self::APPROVE_TYPE;
        }

        if (in_array($marker, $this->getUnderReviewMarkers(), true)) {
            return self::UNDER_REVIEW_TYPE;
        }

        if (in_array($marker, $this->getRejectMarkers(), true)) {
            return self::REJECT_TYPE;
        }

        return null;
    }

    /**
     * Return approve merkers
     *
     * @return string[]
     */
    protected function getApproveMarkers()
    {
        // TODO: Allow custom markers
        return [':+1:'];
    }

    /**
     * Return under review markers
     *
     * @return string[]
     */
    public function getUnderReviewMarkers()
    {
        // TODO: Allow custom markers
        return [':shipit:'];
    }

    /**
     * Return reject markers
     *
     * @return string[]
     */
    public function getRejectMarkers()
    {
        // TODO: Allow custom markers
        return [':-1:'];
    }
}
