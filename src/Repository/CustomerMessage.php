<?php

namespace Tourscool\Repository;

/**
 * @description 用户消息
 */
class CustomerMessage extends Base
{
    // PC平台
    const PLATFORM_PC = 1;
    // 小程序平台
    const PLATFORM_SMALL_PRO = 2;
    // WAP站
    const PLATFORM_WAP = 4;
    // APP
    const PLATFORM_APP = 8;

    // 评论消息
    const SOURCE_REVIEW = 1;
    // 咨询消息
    const SOURCE_QUESTION = 2;
    // 推送活动消息
    const SOURCE_PUSH = 3;

    /**
     * @description 活动通知
     */
    public function pushActivity($customerId, $options = [])
    {
        $page = isset($options['page']) ? $options['page'] : 1;
        $pageSize = isset($options['pageSize']) ? $options['pageSize'] : 10;
        $offset = $pageSize * ($page - 1);
        $platforms = isset($options['platforms']) ? $options['platforms'] : 0;
        $whereString = '';
        if ($platforms) {
            $whereString .= " AND  `platforms` & {$platforms}";
        }
        $countSql = <<<SQL
SELECT count(1) as c FROM `customer_message` WHERE `customer_id` = ? AND `source` = ? {$whereString}
SQL;
        $count = $this->db->fetchColumn($countSql, [$customerId, self::SOURCE_PUSH]);
        if ($count <= $offset) {
            return [
                'count' => $count,
                'data' => [],
            ];
        }
        $sql = <<<SQL
SELECT * FROM `customer_message` WHERE `customer_id` = ? AND `source` = ? {$whereString}  LIMIT {$offset}, {$pageSize}
SQL;
        $messages = $this->db->fetchAll($sql, [$customerId, self::SOURCE_PUSH]);
        return [
            'count' => $count,
            'data' => $messages,
        ];
    }

    /**
     * @description 客服回复消息
     */
    public function customerReply($customerId, $options = [])
    {
        $page = isset($options['page']) ? $options['page'] : 1;
        $pageSize = isset($options['pageSize']) ? $options['pageSize'] : 10;
        $offset = $pageSize * ($page - 1);
        $countSql = <<<SQL
SELECT count(1) as c FROM `customer_message` WHERE `customer_id` = ? AND `source` in (?, ?)
SQL;
        $count = $this->db->fetchColumn($countSql, [$customerId, self::SOURCE_REVIEW, self::SOURCE_QUESTION]);
        if ($count <= $offset) {
            return [
                'count' => $count,
                'data' => [],
            ];
        }
        $sql = <<<SQL
SELECT * FROM `customer_message` WHERE `customer_id` = ? AND `source` in (?, ?)  LIMIT {$offset}, {$pageSize}
SQL;
        $messages = $this->db->fetchAll($sql, [$customerId, self::SOURCE_REVIEW, self::SOURCE_QUESTION]);
        return [
            'count' => $count,
            'data' => $messages,
        ];
    }

    /**
     * @description 查看消息关联的内容(评论, 咨询, 活动推送内容)
     */
    public function messageRelation($messageId, $languageId)
    {
        $message = $this->find('customer_message', 'id', $messageId);
        if (!$message) {
            throw new Exception(self::RECORD_NOT_FOUND, '消息不存在');
        }

        return $this->relation($message['source'], $message['relation_id'], $languageId);
    }

    /**
     * @description 消息关联的内容
     * @param $source
     * @param $relationId
     * @param $languageId
     * @return array
     */
    public function relation($source, $relationId, $languageId)
    {
        if ($source == self::SOURCE_REVIEW) {
            // 回复评论 relation_id 保存的是回复的ID
            $review = $this->find('review', 'review_id', $relationId);
            $reviewDescription = $this->findByAttributes('review_description', ['review_id' => $relationId]);
            $parentView = $this->find('review', 'review_id', $review['parent_review_id']);
            $parentViewDescription = $this->findByAttributes('review_description', ['review_id' => $review['parent_review_id']]);
            $productDescription = $this->findByAttributes('product_description', [
                'product_id' => $review['product_id'],
                'language_id' => $languageId,
            ]);
            return [
                'parent_review' => [
                    'description' => $parentViewDescription['description'],
                    'created' => $parentView['created'],
                ],
                'review' => [
                    'description' => $reviewDescription['description'],
                    'created' => $review['created'],
                ],
                'product' => [
                    'name' => $productDescription['name'],
                    'product_id' => $review['product_id'],
                ],
            ];
        } elseif ($source == self::SOURCE_QUESTION) {
            // 咨询回复 relation_id 保存的是tour_question_id
            $question = $this->find('tour_question', 'tour_question_id', $relationId);
            $productDescription = $this->findByAttributes('product_description', [
                'product_id' => $question['product_id'],
                'language_id' => $languageId,
            ]);
            $answer = $this->findByAttributes('tour_answer', ['tour_question_id' => $question['tour_question_id']]);
            return [
                'question' => [
                    'question' => $question['question'],
                    'date' => $question['date'],
                ],
                'answer' => [
                    'answer' => $answer['answer'],
                    'date' => $answer['data'],
                ],
                'product' => [
                    'name' => $productDescription['name'],
                    'product_id' => $question['product_id'],
                ],
            ];
        } elseif ($source == self::SOURCE_PUSH) {
            // 活动推送
            $push = $this->find('activity_push', 'id', $relationId);
            return $push;
        }
    }

}