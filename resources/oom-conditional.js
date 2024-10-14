(function(blocks, blockEditor, element) {
    const el = element.createElement;
    const metadata = {
        "$schema": "https://schemas.wp.org/trunk/block.json",
        "apiVersion": 3,
        "name": "oom/conditional",
        "version": "0.1.0",
        "title": "OoM conditional",
        "category": "widgets",
        "icon": "smiley",
        "description": "Conditional block container.",
        "textdomain": "order-of-mass",
        "supports": {
            "html": false
        },
        "attributes": {
            "bafiky": {
                "type": "object",
                "default": {
                    "mo": "1",
                    "tu": "1",
                    "we": "1",
                    "th": "1",
                    "fr": "1",
                    "sa": "1",
                    "su": "1"
                }
            }
        },
        "editorScript": "file:./oom-conditional.js",
        "editorStyle": "file:./oom-conditional.css"
    }

    const Edit = (props) => {
        const blockProps = blockEditor.useBlockProps();
        const onChangeSranda = (newSranda) => {
            props.setAttributes({
                bafiky: {
                    ...props.attributes.bafiky,
                    [newSranda.target.dataset.kna]: newSranda.target.checked ? '1' : '0'
                }
            });
        };
        return el(
            'div',
            blockProps,
            el(
                blockEditor.BlockControls,
                {key: 'controls'},
                el(
                    'div',
                    {class: 'oom-conditional-block-controls-container'},

                    el(
                        'input',
                        {type: 'checkbox', id: 'OOM_CONDITIONAL_BLOCK_BAFIKY_MO', 'data-kna': 'mo', value: '1', checked: props.attributes.bafiky.mo === '1', onChange: onChangeSranda}
                    ),
                    el(
                        'label',
                        {for: 'OOM_CONDITIONAL_BLOCK_BAFIKY_MO'},
                        'Mo'
                    ),

                    el(
                        'input',
                        {type: 'checkbox', id: 'OOM_CONDITIONAL_BLOCK_BAFIKY_TU', 'data-kna': 'tu', value: '1', checked: props.attributes.bafiky.tu === '1', onChange: onChangeSranda}
                    ),
                    el(
                        'label',
                        {for: 'OOM_CONDITIONAL_BLOCK_BAFIKY_TU'},
                        'Tu'
                    ),

                    el(
                        'input',
                        {type: 'checkbox', id: 'OOM_CONDITIONAL_BLOCK_BAFIKY_WE', 'data-kna': 'we', value: '1', checked: props.attributes.bafiky.we === '1', onChange: onChangeSranda}
                    ),
                    el(
                        'label',
                        {for: 'OOM_CONDITIONAL_BLOCK_BAFIKY_WE'},
                        'We'
                    ),

                    el(
                        'input',
                        {type: 'checkbox', id: 'OOM_CONDITIONAL_BLOCK_BAFIKY_TH', 'data-kna': 'th', value: '1', checked: props.attributes.bafiky.th === '1', onChange: onChangeSranda}
                    ),
                    el(
                        'label',
                        {for: 'OOM_CONDITIONAL_BLOCK_BAFIKY_TH'},
                        'Th'
                    ),

                    el(
                        'input',
                        {type: 'checkbox', id: 'OOM_CONDITIONAL_BLOCK_BAFIKY_FR', 'data-kna': 'fr', value: '1', checked: props.attributes.bafiky.fr === '1', onChange: onChangeSranda}
                    ),
                    el(
                        'label',
                        {for: 'OOM_CONDITIONAL_BLOCK_BAFIKY_FR'},
                        'Fr'
                    ),

                    el(
                        'input',
                        {type: 'checkbox', id: 'OOM_CONDITIONAL_BLOCK_BAFIKY_SA', 'data-kna': 'sa', value: '1', checked: props.attributes.bafiky.sa === '1', onChange: onChangeSranda}
                    ),
                    el(
                        'label',
                        {for: 'OOM_CONDITIONAL_BLOCK_BAFIKY_SA'},
                        'Sa'
                    ),

                    el(
                        'input',
                        {type: 'checkbox', id: 'OOM_CONDITIONAL_BLOCK_BAFIKY_SU', 'data-kna': 'su', value: '1', checked: props.attributes.bafiky.su === '1', onChange: onChangeSranda}
                    ),
                    el(
                        'label',
                        {for: 'OOM_CONDITIONAL_BLOCK_BAFIKY_SU'},
                        'Su'
                    ),
                )
            ),
            el(blockEditor.InnerBlocks)
        );
        //return element.createElement('p', blockProps, 'Edit content');
    }

    const Save = () => {
        const blockProps = blockEditor.useBlockProps.save();
        return el(blockEditor.InnerBlocks.Content);
        //return element.createElement('p', blockProps, 'Save content');
    }

    blocks.registerBlockType(metadata.name, {
        ...metadata,
        edit: Edit,
        save: Save,
    });
})(window.wp.blocks, window.wp.blockEditor, window.wp.element);
