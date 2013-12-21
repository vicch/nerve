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
    </div>
    <?= $this->Form->end() ?>
</div>