<div id="rightbar" class="sidebar">
    <?= $this->Form->create(
        'rForm',
        array(
            'id' => 'rForm',
            'url' => array(
                'controller' => 'top',
                'action'     => 'addAll',
            ),
            'inputDefaults' => array(
                'label' => false,
                'div'   => false
            ),
        )
    ); ?>
    <div class="rightbox" id="frombox">
        <div class='rightboxrow'>
            <div class='word-field'>
                <?= $this->Form->input('wFromId', array(
                    'type'  => 'hidden',
                )) ?>
                <?= $this->Form->input('wFromWord', array(
                    'type'  => 'text',
                    'class' => 'word',
                )) ?>
                <?= $this->Form->input('wFromLanguage', array(
                    'class'    => 'lang',
                    'options'  => $selectLangs,
                    'autocomplete' => 'off',
                )) ?>
            </div>
            <?= $this->Form->submit('+', array(
                'class' => 'submit',
                'name'  => 'addWFrom',
            )); ?>
            <!-- <div class="submit">
                <?= $this->Form->button('+', array(
                    'type' => 'button',
                    'id'   => 'addWFrom',
                )); ?>
            </div> -->
        </div>
        <?php foreach($data['senses'] as $sense): ?>
            <?php if(!empty($sense['id'])): ?>
                <div class='rightboxrow'>
                    <div class='sense-field'>
                        <?= $this->Form->input('sFrom', array(
                            'type'    => 'radio',
                            'options' => array($sense['id'] => ''),
                        )) ?>
                        <?= $this->Form->input('sFromId_' . $sense['id'], array(
                            'type'  => 'hidden',
                        )) ?>
                        <?= $this->Form->input('sFromPos_' . $sense['id'], array(
                            'type'  => 'text',
                            'class' => 'pos',
                        )) ?>
                        <?= $this->Form->input('sFromOrder_' . $sense['id'], array(
                            'type'  => 'hidden',
                        )) ?>
                        <?= $this->Form->input('sFromMng_' . $sense['id'], array(
                            'type'  => 'text',
                            'class' => 'meaning',
                        )) ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <div class='rightboxrow'>
            <div class='sense-field new'>
                <?= $this->Form->input('sFromPosNew', array(
                    'type'  => 'text',
                    'class' => 'pos',
                )) ?>
                <?= $this->Form->input('sFromMngNew', array(
                    'type'  => 'text',
                    'class' => 'meaning',
                )) ?>
            </div>
            <?= $this->Form->submit('+', array(
                'class' => 'submit',
                'name'  => 'addSFrom',
            )); ?>
        </div>
    </div>
    <hr />
    <div class="rightbox" id="relationbox">
        <div class='rightboxrow'>
            <div class='word-field'>
                <?= $this->Form->input('reId', array(
                    'type'  => 'hidden',
                )) ?>
                <?= $this->Form->input('reType', array(
                    'class'    => 'reType',
                    'options'  => $selectReTypes,
                    'autocomplete' => 'off',
                )) ?>
            </div>
            <?= $this->Form->submit('+', array(
                'class' => 'submit',
                'name'  => 'addRe',
            )); ?>
            <div class='detail-field'>
                <?= $this->Form->input('reDetailId', array(
                    'type'  => 'hidden',
                )) ?>
                <?= $this->Form->input('reDetailText', array(
                    'type'     => 'textarea',
                    'class'    => 'reDetail',
                )) ?>
            </div>
        </div>
    </div>
    <hr />
    <div class="rightbox" id="tobox">
        <div class='rightboxrow'>
            <div class='word-field'>
                <?= $this->Form->input('wToId', array(
                    'type'  => 'hidden',
                )) ?>
                <?= $this->Form->input('wToWord', array(
                    'type'  => 'text',
                    'class' => 'word',
                )) ?>
                <?= $this->Form->input('wToLanguage', array(
                    'class'    => 'lang',
                    'options'  => $selectLangs,
                    'autocomplete' => 'off',
                )) ?>
            </div>
            <?= $this->Form->submit('+', array(
                'class' => 'submit',
                'name'  => 'addWTo',
            )); ?>
        </div>
        <!-- <div class='rightboxrow'>
            <div class='sense-field new'>
                <?= $this->Form->input('sToPosNew', array(
                    'type'  => 'text',
                    'class' => 'pos',
                )) ?>
                <?= $this->Form->input('sToMngNew', array(
                    'type'  => 'text',
                    'class' => 'meaning',
                )) ?>
            </div>
            <?= $this->Form->submit('+', array(
                'class' => 'submit',
                'name'  => 'addSTo',
            )); ?>
        </div> -->
    </div>
    <?= $this->Form->end() ?>
</div>