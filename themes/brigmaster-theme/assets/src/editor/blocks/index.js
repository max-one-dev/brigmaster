import { blockAttributes, blockDefinitions } from './definitions.js';
import { renderFields } from './fields.js';

const { createElement: h, Fragment } = window.wp.element;
const { registerBlockType, getBlockType } = window.wp.blocks;
const { InspectorControls, useBlockProps, InnerBlocks } = window.wp.blockEditor;
const { PanelBody } = window.wp.components;
const ServerSideRender = window.wp.serverSideRender;

// Blocks whose body is authored as nested Gutenberg blocks (InnerBlocks) instead
// of a ServerSideRender preview. The server render_callback wraps the inner
// content in the block's chrome (sidebar, layout) at render time.
function registerInnerBlocksBlock(definition) {
  if (getBlockType(definition.name)) {
    return;
  }

  registerBlockType(definition.name, {
    apiVersion: 3,
    title: definition.title,
    category: 'constructly',
    supports: {
      anchor: false,
      customClassName: false,
      html: false,
      align: false,
      inserter: definition.inserter !== false,
    },
    attributes: blockAttributes,
    edit() {
      const blockProps = useBlockProps({ className: 'bm-editor-block-preview bm-editor-innerblocks' });
      return h(
        'div',
        blockProps,
        h(InnerBlocks, { templateLock: false }),
      );
    },
    save() {
      return h(InnerBlocks.Content);
    },
  });
}

function registerConstructlyBlock(definition) {
  if (definition.innerBlocks) {
    registerInnerBlocksBlock(definition);
    return;
  }

  if (getBlockType(definition.name)) {
    return;
  }

  registerBlockType(definition.name, {
    apiVersion: 3,
    title: definition.title,
    category: 'constructly',
    supports: {
      anchor: false,
      customClassName: false,
      html: false,
      align: false,
      inserter: definition.inserter !== false,
    },
    attributes: blockAttributes,
    edit({ attributes, setAttributes }) {
      const blockProps = useBlockProps({
        className: 'bm-editor-block-preview',
      });

      return h(
        Fragment,
        null,
        h(
          InspectorControls,
          null,
          h(
            PanelBody,
            {
              title: 'Основные поля',
              initialOpen: true,
            },
            renderFields(definition.fields, attributes, setAttributes),
          ),
        ),
        h(
          'div',
          blockProps,
          h(ServerSideRender, {
            block: definition.name,
            attributes,
          }),
        ),
      );
    },
    save() {
      return null;
    },
  });
}

blockDefinitions.forEach(registerConstructlyBlock);
