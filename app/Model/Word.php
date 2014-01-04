<?php

class Word extends AppModel {
    
    const INIT_MIN_RE = 3;
    
    private $__selectLangs = array(
        '',
        'en' => 'En',
        'fr' => 'Fr',
        'jp' => 'Jp',
        'ch' => 'Ch',
    );
    
    public function getSelectLangs() {
        return $this->__selectLangs;
    }
    
    public function queryForView($word, $language = NULL) {
        
        // fields
        $fields = array(
            'Word.id',
            'Word.language',
            'Word.word',
            'Senses.id',
            'Senses.pos',
            'Senses.order_num',
            'Senses.meaning',
            'Relation.id',
            'Relation.parent_id',
            'Relation.sense_id_from',
            'Relation.sense_id_to',
            'WordTo.id',
            'WordTo.language',
            'WordTo.word',
            'SensesTo.id',
            'SensesTo.pos',
            'SensesTo.order_num',
            'SensesTo.meaning',
            'Type.id',
            'Type.type',
            'Type.related_type_id',
            'Detail.id',
            'Detail.text',
        );
        
        // joins
        $joins = array(
            array(
                'alias'      => 'Senses',
                'table'      => 'word_senses',
                'type'       => 'left',
                'conditions' => array(
                    'Word.id = Senses.word_id',
                    'Senses.deleted'  => AppModel::ENTRY_NOT_DELETED,
                ),
            ),
            array(
                'alias'      => 'Relation',
                'table'      => 'relations',
                'type'       => 'left',
                'conditions' => array(
                    'Word.id = Relation.word_id_from',
                    'Relation.deleted'  => AppModel::ENTRY_NOT_DELETED,
                ),
            ),
            array(
                'alias'      => 'WordTo',
                'table'      => 'words',
                'type'       => 'left',
                'conditions' => array(
                    'Relation.word_id_to = WordTo.id',
                    'WordTo.deleted'  => AppModel::ENTRY_NOT_DELETED,
                ),
            ),
            array(
                'alias'      => 'SensesTo',
                'table'      => 'word_senses',
                'type'       => 'left',
                'conditions' => array(
                    'WordTo.id = SensesTo.word_id',
                    'SensesTo.deleted'  => AppModel::ENTRY_NOT_DELETED,
                ),
            ),
            array(
                'alias'      => 'Type',
                'table'      => 'relation_types',
                'type'       => 'left',
                'conditions' => array(
                    'Relation.relation_type_id = Type.id',
                    'Type.deleted'  => AppModel::ENTRY_NOT_DELETED,
                ),
            ),
            array(
                'alias'      => 'Detail',
                'table'      => 'relation_details',
                'type'       => 'left',
                'conditions' => array(
                    'Relation.relation_detail_id = Detail.id',
                    'Detail.deleted'  => AppModel::ENTRY_NOT_DELETED,
                ),
            ),
        );
        
        // conditions
        $conditions = array(
            'Word.deleted'  => AppModel::ENTRY_NOT_DELETED,
        );
        if (!empty($word)) {
            $wordCondition = array('Word.word' => $word);
        } else {
            // If no queried word,
            // display word with no less than INIT_MIN_RE relations,
            // sorting by relation created time from latest to earliest
            $minRe = self::INIT_MIN_RE;
            $wordCondition = array("Word.id = (
                SELECT
                r.word_id_from
                FROM relations r
                LEFT JOIN (
                    SELECT
                    id,
                    COUNT(id) AS count
                    FROM relations
                    GROUP BY word_id_from
                ) rc ON r.id = rc.id
                WHERE rc.count >= $minRe
                ORDER BY created DESC
                LIMIT 1
            )");
        }
        $conditions = array_merge($conditions, $wordCondition);
        
        // order
        // $order = array(
            // 'Senses.pos ASC',
            // 'Senses.order_num ASC',
        // );
        
        return array(
            'fields'     => $fields,
            'joins'      => $joins,
            'conditions' => $conditions,
        );
    }
    
    public function formatForView($queryResult) {
        $formatResult = array();
        if (!empty($queryResult)) {
            $formatResult['word']      = $queryResult[0]['Word'];
            $formatResult['senses']    = array();
            $formatResult['relations'] = array();
            foreach ($queryResult as $index => $record) {
                // array_push($formatResult['senses'], $record['Senses']);
                $formatResult['senses'][$record['Senses']['id']] = $record['Senses'];
                if (!empty($record['Relation']['id'])) {
                    $formatResult['relations'][] = array(
                        'relation'  => $record['Relation'],
                        'type'      => $record['Type'],
                        'detail'    => $record['Detail'],
                        'word_to'   => $record['WordTo'],
                        'senses_to' => $record['SensesTo'],
                    );
                }
            }
        }
        return $formatResult;
    }
    
    public function addWord($postWord) {
        
        $wordData = array(
            'word'     => $postWord['word'],
            'language' => $postWord['language'],
        );
        
        $checkConditions = array(
            'deleted'  => AppModel::ENTRY_NOT_DELETED,
        );
        $checkConditions = array_merge($checkConditions, $wordData);
        
        $wordExist = $this->find('first', array('conditions' => $checkConditions));
        
        if (empty($wordExist)) {
            $word = new Word;
            $word->set($wordData);
            $word->save();
            $wordData['id'] = $word->getLastInsertId();
        } else {
            $wordData['id'] = $wordExist['Word']['id'];
        }
    
        return $wordData;
    }
}
