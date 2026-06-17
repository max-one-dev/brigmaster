const textField = (name, label) => ({ type: 'text', name, label });
const textareaField = (name, label) => ({ type: 'textarea', name, label });
const imageField = (name, label) => ({ type: 'image', name, label });

const linkFields = [
  textField('linkLabel', 'Текст ссылки'),
  textField('linkUrl', 'URL ссылки'),
];

const sectionFields = [
  textField('anchor', 'Якорь секции'),
  textField('titleId', 'ID заголовка'),
];

export const blockAttributes = {
  title: { type: 'string' },
  lead: { type: 'string' },
  subtitle: { type: 'string' },
  text: { type: 'string' },
  anchor: { type: 'string' },
  anchorId: { type: 'string' },
  sectionId: { type: 'string' },
  sectionTitle: { type: 'string' },
  titleId: { type: 'string' },
  variant: { type: 'string' },
  columns: { type: 'string' },
  mediaPosition: { type: 'string' },
  imageAlt: { type: 'string' },
  primaryLabel: { type: 'string' },
  primaryUrl: { type: 'string' },
  secondaryLabel: { type: 'string' },
  secondaryUrl: { type: 'string' },
  linkLabel: { type: 'string' },
  linkUrl: { type: 'string' },
  buttonLabel: { type: 'string' },
  buttonUrl: { type: 'string' },
  image: { type: 'string' },
  note: { type: 'string' },
  quickLinksLabel: { type: 'string' },
  themeVariant: { type: 'string' },
  ctaLabel: { type: 'string' },
  ctaUrl: { type: 'string' },
  aside: { type: 'string' },
  shortcode: { type: 'string' },
  infoTitle: { type: 'string' },
  infoText: { type: 'string' },
  infoBody: { type: 'string' },
  methodTitle: { type: 'string' },
  noteText: { type: 'string' },
  noteLinkLabel: { type: 'string' },
  noteLinkUrl: { type: 'string' },
  features: { type: 'array', default: [] },
  breadcrumbs: { type: 'array', default: [] },
  cards: { type: 'array', default: [] },
  items: { type: 'array', default: [] },
  steps: { type: 'array', default: [] },
  methodItems: { type: 'array', default: [] },
  paragraphs: { type: 'array', default: [] },
  demo: { type: 'object', default: {} },
};

export const blockDefinitions = [
  {
    name: 'constructly/home-hero',
    title: 'Constructly Hero',
    fields: [
      textField('title', 'Заголовок'),
      textareaField('lead', 'Описание'),
      textField('primaryLabel', 'Текст основной кнопки'),
      textField('primaryUrl', 'URL основной кнопки'),
      textField('secondaryLabel', 'Текст второй ссылки'),
      textField('secondaryUrl', 'URL второй ссылки'),
      textareaField('note', 'Примечание'),
      {
        type: 'repeater',
        name: 'features',
        label: 'Преимущества',
        itemFields: [
          textField('icon', 'Иконка'),
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
        ],
      },
      {
        type: 'object',
        name: 'demo',
        label: 'Демо-калькулятор',
        fields: [
          textField('title', 'Заголовок'),
          textField('resultLabel', 'Метка результата'),
          textField('resultSummary', 'Описание результата'),
          textField('resultValue', 'Значение результата'),
        ],
      },
    ],
  },
  {
    name: 'constructly/page-hero',
    title: 'Constructly Page Hero',
    fields: [
      textField('titleId', 'ID заголовка'),
      imageField('image', 'Фоновое изображение'),
      {
        type: 'repeater',
        name: 'breadcrumbs',
        label: 'Хлебные крошки',
        itemFields: [
          textField('label', 'Текст'),
          textField('url', 'URL'),
        ],
      },
      textField('title', 'Заголовок'),
      textareaField('lead', 'Лид'),
      {
        type: 'repeater',
        name: 'paragraphs',
        label: 'Абзацы',
        itemFields: [
          textareaField('text', 'Текст'),
        ],
      },
    ],
  },
  {
    name: 'constructly/feature-cards',
    title: 'Constructly Feature Cards',
    fields: [
      textField('titleId', 'ID заголовка'),
      textField('sectionTitle', 'Заголовок секции'),
      textField('columns', 'Колонки (3 или 4)'),
      textField('variant', 'Вариант (stacked)'),
      {
        type: 'repeater',
        name: 'items',
        label: 'Карточки',
        itemFields: [
          textField('icon', 'Иконка'),
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
        ],
      },
    ],
  },
  {
    name: 'constructly/text-media',
    title: 'Constructly Text Media',
    fields: [
      textField('titleId', 'ID заголовка'),
      textField('title', 'Заголовок'),
      textField('mediaPosition', 'Позиция изображения (left/right)'),
      imageField('image', 'Изображение'),
      textField('imageAlt', 'Alt изображения'),
      {
        type: 'repeater',
        name: 'paragraphs',
        label: 'Абзацы',
        itemFields: [
          textareaField('text', 'Текст'),
        ],
      },
    ],
  },
  {
    name: 'constructly/foundation-hub-hero',
    title: 'Constructly Foundation Hub Hero',
    fields: [
      textField('titleId', 'ID заголовка'),
      imageField('image', 'Изображение'),
      {
        type: 'repeater',
        name: 'breadcrumbs',
        label: 'Хлебные крошки',
        itemFields: [
          textField('label', 'Текст'),
          textField('url', 'URL'),
        ],
      },
      textField('title', 'Заголовок'),
      textareaField('lead', 'Описание'),
      {
        type: 'repeater',
        name: 'features',
        label: 'Преимущества',
        itemFields: [
          textField('icon', 'Иконка'),
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
        ],
      },
    ],
  },
  {
    name: 'constructly/popular-calculators',
    title: 'Constructly Popular Calculators',
    fields: [
      textField('anchor', 'Якорь'),
      textField('title', 'Заголовок'),
      textareaField('subtitle', 'Описание'),
      ...linkFields,
      {
        type: 'repeater',
        name: 'cards',
        label: 'Карточки',
        itemFields: [
          textField('title', 'Заголовок'),
          textareaField('description', 'Описание'),
          textField('buttonLabel', 'Текст кнопки'),
          textField('buttonUrl', 'URL кнопки'),
          textField('icon', 'Иконка'),
        ],
      },
    ],
  },
  {
    name: 'constructly/calculator-hero',
    title: 'Constructly Calculator Hero',
    fields: [
      textField('titleId', 'ID заголовка'),
      {
        type: 'repeater',
        name: 'breadcrumbs',
        label: 'Хлебные крошки',
        itemFields: [
          textField('label', 'Текст'),
          textField('url', 'URL'),
        ],
      },
      textField('title', 'Заголовок'),
      textareaField('lead', 'Описание'),
      {
        type: 'repeater',
        name: 'features',
        label: 'Особенности',
        itemFields: [
          textField('icon', 'Иконка'),
          textField('text', 'Текст'),
        ],
      },
    ],
  },
  {
    name: 'constructly/calculator-estimator',
    title: 'Constructly Calculator Estimator',
    fields: [
      textField('infoTitle', 'Заголовок'),
      textareaField('infoBody', 'Контент'),
      textField('noteText', 'Текст примечания'),
      textField('noteLinkLabel', 'Текст ссылки'),
      textField('noteLinkUrl', 'URL ссылки'),
    ],
  },
  {
    name: 'constructly/tasks',
    title: 'Constructly Tasks',
    fields: [
      ...sectionFields,
      textField('variant', 'Вариант'),
      textField('title', 'Заголовок'),
      textareaField('subtitle', 'Описание'),
      {
        type: 'repeater',
        name: 'items',
        label: 'Задачи',
        itemFields: [
          textField('icon', 'Иконка'),
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
          textField('label', 'Текст ссылки'),
          textField('url', 'URL'),
          imageField('image', 'Изображение'),
        ],
      },
    ],
  },
  {
    name: 'constructly/foundation-hub-type-cards',
    title: 'Constructly Foundation Hub Types',
    fields: [
      textField('anchorId', 'Якорь секции'),
      textField('titleId', 'ID заголовка'),
      textField('sectionTitle', 'Заголовок'),
      textareaField('subtitle', 'Описание'),
      ...linkFields,
      {
        type: 'repeater',
        name: 'cards',
        label: 'Калькуляторы',
        itemFields: [
          imageField('image', 'Изображение'),
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
          textField('href', 'URL'),
          textField('cta', 'Текст кнопки'),
        ],
      },
    ],
  },
  {
    name: 'constructly/how-it-works',
    title: 'Constructly How It Works',
    fields: [
      textField('anchor', 'Якорь'),
      textField('title', 'Заголовок'),
      textareaField('subtitle', 'Описание'),
      {
        type: 'repeater',
        name: 'steps',
        label: 'Шаги',
        itemFields: [
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
          textField('icon', 'Иконка'),
        ],
      },
    ],
  },
  {
    name: 'constructly/trust',
    title: 'Constructly Trust',
    fields: [
      ...sectionFields,
      textField('themeVariant', 'Вариант фона'),
      textField('title', 'Заголовок'),
      textareaField('subtitle', 'Описание'),
      ...linkFields,
      {
        type: 'repeater',
        name: 'items',
        label: 'Пункты',
        itemFields: [
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
          textField('icon', 'Иконка'),
        ],
      },
    ],
  },
  {
    name: 'constructly/articles',
    title: 'Constructly Articles',
    fields: [
      textField('title', 'Заголовок'),
      ...linkFields,
      {
        type: 'repeater',
        name: 'items',
        label: 'Статьи',
        itemFields: [
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
          textField('url', 'URL'),
          imageField('image', 'Изображение'),
          textField('imageAlt', 'Alt изображения'),
          textField('tag', 'Рубрика'),
          textField('readTime', 'Время чтения'),
          textField('date', 'Дата'),
        ],
      },
    ],
  },
  {
    name: 'constructly/faq',
    title: 'Constructly FAQ',
    fields: [
      textField('sectionId', 'ID секции'),
      textField('titleId', 'ID заголовка'),
      textField('title', 'Заголовок'),
      ...linkFields,
      {
        type: 'repeater',
        name: 'items',
        label: 'Вопросы',
        itemFields: [
          textField('question', 'Вопрос'),
          textareaField('answer', 'Ответ'),
        ],
      },
    ],
  },
  {
    name: 'constructly/final-cta',
    title: 'Constructly Final CTA',
    fields: [
      textField('titleId', 'ID заголовка'),
      textField('variant', 'Вариант'),
      textField('title', 'Заголовок'),
      textareaField('text', 'Текст'),
      textField('buttonLabel', 'Текст кнопки'),
      textField('buttonUrl', 'URL кнопки'),
      imageField('image', 'Изображение'),
    ],
  },
  {
    name: 'constructly/home-tasks',
    title: 'Constructly Tasks',
    inserter: false,
    fields: [
      ...sectionFields,
      textField('title', 'Заголовок'),
      textareaField('subtitle', 'Описание'),
      {
        type: 'repeater',
        name: 'items',
        label: 'Задачи',
        itemFields: [
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
          textField('label', 'Текст ссылки'),
          textField('url', 'URL'),
          imageField('image', 'Изображение'),
        ],
      },
    ],
  },
  {
    name: 'constructly/home-articles',
    title: 'Constructly Articles',
    inserter: false,
    fields: [
      textField('title', 'Заголовок'),
      ...linkFields,
      {
        type: 'repeater',
        name: 'items',
        label: 'Статьи',
        itemFields: [
          textField('title', 'Заголовок'),
          textareaField('text', 'Текст'),
          textField('url', 'URL'),
          imageField('image', 'Изображение'),
          textField('imageAlt', 'Alt изображения'),
          textField('tag', 'Рубрика'),
          textField('readTime', 'Время чтения'),
          textField('date', 'Дата'),
        ],
      },
    ],
  },
  {
    name: 'constructly/home-faq',
    title: 'Constructly FAQ',
    inserter: false,
    fields: [
      textField('sectionId', 'ID секции'),
      textField('titleId', 'ID заголовка'),
      textField('title', 'Заголовок'),
      ...linkFields,
      {
        type: 'repeater',
        name: 'items',
        label: 'Вопросы',
        itemFields: [
          textField('question', 'Вопрос'),
          textareaField('answer', 'Ответ'),
        ],
      },
    ],
  },
];
