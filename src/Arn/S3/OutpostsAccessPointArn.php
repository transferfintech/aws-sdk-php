<?php
namespace Aws\Arn\S3;

use Aws\Arn\AccessPointArn;
use Aws\Arn\Arn;
use Aws\Arn\ArnInterface;
use Aws\Arn\Exception\InvalidArnException;

/**
 * This class represents an S3 Outposts access point ARN, which is in the
 * following format:
 *
 * arn:{partition}:s3-outposts:{region}:{accountId}:outpost/{outpostId}/accesspoint/{accesspointName}
 *
 * In relation to a normal access point ARN, the resource type must be 'outpost',
 * and the resource ID must be in the following format:
 *
 * {outpostId}/accesspoint/{accesspointName}
 *
 * ':' and '/' can be used interchangeably as delimiters within the resource type
 * and resource ID.
 *
 * @internal
 */
class OutpostsAccessPointArn extends AccessPointArn implements ArnInterface
{
    public static function parse($string)
    {
        $data = parent::parse($string);
        return self::parseOutpostData($data);
    }

    public function getOutpostId()
    {
        return $this->data['outpost_id'];
    }

    public function getAccesspointId()
    {
        return $this->data['accesspoint_id'];
    }

    private static function parseOutpostData(array $data)
    {
        $resourceData = preg_split("/[\/:]/", $data['resource_id']);

        $data['outpost_id'] = isset($resourceData[0])
            ? $resourceData[0]
            : null;
        $data['accesspoint_type'] = isset($resourceData[1])
            ? $resourceData[1]
            : null;
        $data['accesspoint_id'] = isset($resourceData[2])
            ? $resourceData[2]
            : null;

        return $data;
    }

    /**
     * Validation specific to OutpostsAccessPointArn. Note this uses the base Arn
     * class validation instead of the direct parent due to it having slightly
     * differing requirements from its parent.
     *
     * @param array $data
     */
    protected static function validate(array $data)
    {
        Arn::validate($data);

        if (($data['service'] !== 's3-outposts')) {
            throw new InvalidArnException("The 3rd component of an S3 Outposts"
                . " access point ARN represents the service and must be"
                . " 's3-outposts'.");
        }

        if (empty($data['region'])) {
            throw new InvalidArnException("The 4th component of an access point ARN"
                . " represents the region and must not be empty.");
        }

        if (!self::isValidHostLabel($data['account_id'])) {
            throw new InvalidArnException("The 5th component of an S3 Outposts"
                . " access point ARN is required, represents the account ID, and"
                . " must be a valid host label.");
        }

        if (($data['resource_type'] !== 'outpost')) {
            throw new InvalidArnException("The 6th component of an S3 Outposts"
                . " access point ARN represents the resource type and must be"
                . " 'outpost'.");
        }

        if (!self::isValidHostLabel($data['outpost_id'])) {
            throw new InvalidArnException("The 7th component of an S3 Outposts"
                . " access point ARN is required, represents the outpost ID, and"
                . " must be a valid host label.");
        }

        if ($data['accesspoint_type'] !== 'accesspoint') {
            throw new InvalidArnException("The 8th component of an S3 Outposts"
                . " access point ARN must be 'accesspoint'");
        }

        if (!self::isValidHostLabel($data['accesspoint_id'])) {
            throw new InvalidArnException("The 9th component of an S3 Outposts"
                . " access point ARN is required, represents the accesspoint ID,"
                . " and must be a valid host label.");
        }
    }
}
