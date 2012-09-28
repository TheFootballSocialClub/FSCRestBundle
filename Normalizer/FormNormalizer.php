<?php

namespace FSC\RestBundle\Normalizer;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

use FSC\RestBundle\Model\Representation\Form as FormRepresentations;
use FSC\RestBundle\REST\Description\FormDescription;
use FSC\RestBundle\REST\AtomLinkFactory;

/**
 * FormNormalizer
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class FormNormalizer
{
    /**
     * @var AtomLinkFactory
     */
    protected $atomLinkFactory;

    public function __construct(AtomLinkFactory $atomLinkFactory = null)
    {
        $this->atomLinkFactory = $atomLinkFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null)
    {
        if ($object instanceof Form) {
            $object = $object->createView();
        }

        $formView = $object; /** @var $formView FormView */

        $formElements = $this->renderBlock($formView, 'rest');
        $formElements = $this->mergeFormElementsArrays($formElements);

        $formRepresentation = new FormRepresentations\Form();

        $formRepresentation->inputs = array_values(array_filter($formElements, function ($element) {
            return $element instanceof FormRepresentations\Input;
        }));

        $formRepresentation->textareas = array_values(array_filter($formElements, function ($element) {
            return $element instanceof FormRepresentations\Textarea;
        }));

        $formRepresentation->selects = array_values(array_filter($formElements, function ($element) {
            return $element instanceof FormRepresentations\Select;
        }));

        return $formRepresentation;
    }

    public function normalizeFormDescription(FormDescription $formDescription)
    {
        $formRepresentation = $this->normalize($formDescription->getForm());
        $formRepresentation->rel = $formDescription->getRel();
        $formRepresentation->method = $formDescription->getMethod();
        $formRepresentation->action = $formDescription->getActionUrl();

        if (null !== $this->atomLinkFactory) {
            $formRepresentation->addLink($this->atomLinkFactory->create('self', $formDescription->getSelfUrl()));
        }

        return $formRepresentation;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Form || $data instanceof FormView;
    }

    protected function mergeFormElementsArrays($formElements)
    {
        $arrayElements = array_filter($formElements, function ($element) {
            return is_array($element);
        });

        if (0 == count($arrayElements)) {
            return $formElements;
        }

        $formElements = array_filter($formElements, function ($element) {
            return !is_array($element);
        });

        foreach ($arrayElements as $arrayElement) {
            $formElements = array_merge($formElements, $arrayElement);
        }

        return $this->mergeFormElementsArrays($formElements);
    }

    protected function renderBlock(FormView $view, $blockName = null, array $variables = array())
    {
        $variables = $view->getVars();

        $type = null;
        foreach ($variables['block_prefixes'] as $blockPrefix) {
            if (in_array($blockPrefix, array(
                'textarea', 'email', 'integer', 'money', 'number', 'password', 'percent', 'search', 'url', 'hidden',
                'collection', 'choice',
            ))
            ) {
                $type = $blockPrefix;
            }
        }

        if ('rest' == $blockName) {
            return $this->renderFormRest($view, $blockName, $variables);
        }

        switch ($type) {
            // Text fields
            case 'textarea': return $this->renderTextareaWidget($view, $blockName, $variables);
            case 'email': return $this->renderEmailWidget($view, $blockName, $variables);
            case 'integer': return $this->renderIntegerWidget($view, $blockName, $variables);
            case 'money': return $this->renderMoneyWidget($view, $blockName, $variables);
            case 'number': return $this->renderNumberWidget($view, $blockName, $variables);
            case 'password': return $this->renderPasswordWidget($view, $blockName, $variables);
            case 'percent': return $this->renderPercentWidget($view, $blockName, $variables);
            case 'search': return $this->renderSearchWidget($view, $blockName, $variables);
            case 'url': return $this->renderUrlWidget($view, $blockName, $variables);
            case 'hidden': return $this->renderHiddenWidget($view, $blockName, $variables);
            case 'collection': return $this->renderCollectionWidget($view, $blockName, $variables);
            case 'choice': return $this->renderChoiceWidget($view, $blockName, $variables);
        }

        switch ($blockName) {
            case 'widget': return $this->renderFormWidget($view, $blockName, $variables);
            case 'row': return $this->renderFormRow($view, $blockName, $variables);
        }

        throw new \Exception(__METHOD__.' Oups '.$view->getName().' // '.$blockName.' // '.$blockNameSuffix);
    }

    /*
        {% if compound %}
            {{ block('form_widget_compound') }}
        {% else %}
            {{ block('form_widget_simple') }}
        {% endif %}
     */
    protected function renderFormWidget(FormView $view, $blockName, array $variables = array())
    {
        return $variables['compound']
            ? $this->renderWidgetCompound($view, $blockName, $variables)
            : $this->renderWidgetSimple($view, $blockName, $variables)
            ;
    }

    /*
        {% set type = type|default('text') %}
        <input type="{{ type }}" {{ block('widget_attributes') }} {% if value is not empty %}value="{{ value }}" {% endif %}/>
     */
    protected function renderWidgetSimple(FormView $view, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'text';

        $input = new FormRepresentations\Input();
        $input->attributes['type'] = $variables['type'];

        if (!empty($variables['value'])) {
            $input->attributes['value'] = $variables['value'];
        }

        $this->addWidgetAttributes($input, $view, $blockName, $variables);

        return $input;
    }

    /*
        <div {{ block('widget_container_attributes') }}>
            {% if form.parent is empty %}
                {{ form_errors(form) }}
            {% endif %}
            {{ block('form_rows') }}
            {{ form_rest(form) }}
        </div>
    */
    protected function renderWidgetCompound(FormView $view, $blockName, array $variables = array())
    {
        return $this->renderFormRows($view, $blockName, $variables);
    }

    /*
        <div>
            {{ form_label(form) }}
            {{ form_errors(form) }}
            {{ form_widget(form) }}
        </div>
     */
    protected function renderFormRow(FormView $view, $blockName, array $variables = array())
    {
        return $this->renderBlock($view, 'widget');
    }

    /*
        {% for child in form %}
            {% if not child.rendered %}
                {{ form_row(child) }}
            {% endif %}
        {% endfor %}
     */
    protected function renderFormRest(FormView $view, $blockName, array $variables = array())
    {
        $renderedView = array();

        // TODO, here add the rendered views to a form object, or something

        foreach ($view->getChildren() as $childView) {
            $renderedView[] = $this->renderBlock($childView, 'row');
        }

        return $renderedView;
    }

    /*
        <textarea {{ block('widget_attributes') }}>{{ value }}</textarea>
     */
    protected function renderTextareaWidget(FormView $view, $blockName, array $variables = array())
    {
        $widget = new FormRepresentations\Textarea();

        $widget->value = $variables['value'];

        $this->addWidgetAttributes($widget, $view, $blockName, $variables);

        return $widget;
    }

    /*
        {% set type = type|default('email') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderEmailWidget(FormView $view, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'email';

        return $this->renderWidgetSimple($view, $blockName, $variables);
    }

    /*
        {% set type = type|default('number') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderIntegerWidget(FormView $view, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'integer';

        return $this->renderWidgetSimple($view, $blockName, $variables);
    }

    /*
        {{ money_pattern|replace({ '{{ widget }}': block('form_widget_simple') })|raw }}
    */
    protected function renderMoneyWidget(FormView $view, $blockName, array $variables = array())
    {
        throw new \Exception(__METHOD__.' not implemented');
    }

    /*
        {# type="number" doesn't work with floats #}
        {% set type = type|default('text') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderNumberWidget(FormView $view, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'number';

        return $this->renderWidgetSimple($view, $blockName, $variables);
    }

    /*
        {% set type = type|default('password') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderPasswordWidget(FormView $view, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'password';

        return $this->renderWidgetSimple($view, $blockName, $variables);
    }

    /*
        {% set type = type|default('text') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderPercentWidget(FormView $view, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'text';

        return $this->renderWidgetSimple($view, $blockName, $variables);
    }

    /*
        {% set type = type|default('search') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderSearchWidget(FormView $view, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'search';

        return $this->renderWidgetSimple($view, $blockName, $variables);
    }

    /*
        {% set type = type|default('url') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderUrlWidget(FormView $view, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'url';

        return $this->renderWidgetSimple($view, $blockName, $variables);
    }

    /*
        {% set type = type|default('hidden') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderHiddenWidget(FormView $view, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'hidden';

        return $this->renderWidgetSimple($view, $blockName, $variables);
    }


    /*
        {% if prototype is defined %}
            {% set attr = attr|merge({'data-prototype': form_row(prototype) }) %}
        {% endif %}
        {{ block('form_widget') }}
    */
    protected function renderCollectionWidget(FormView $view, $blockName, array $variables = array())
    {
        if (isset($variables['prototype'])) {
            $variables['attr'] = array_merge($variables['attr'], array(
                'data-prototype' => $this->renderer->searchAndRenderBlock($variables['prototype'], 'row'),
            ));
        }

        return $this->renderFormWidget($view, $blockName, $variables);
    }

    /*
        {% if expanded %}
            {{ block('choice_widget_expanded') }}
        {% else %}
            {{ block('choice_widget_collapsed') }}
        {% endif %}
    */
    protected function renderChoiceWidget(FormView $view, $blockName, array $variables = array())
    {
        return $variables['expanded']
            ? $this->renderChoiceWidgetExpanded($view, $blockName, $variables)
            : $this->renderChoiceWidgetCollapsed($view, $blockName, $variables)
            ;
    }

    /*
        <div {{ block('widget_container_attributes') }}>
        {% for child in form %}
            {{ form_widget(child) }}
            {{ form_label(child) }}
        {% endfor %}
        </div>
    */
    protected function renderChoiceWidgetExpanded(FormView $view, $blockName, array $variables = array())
    {
        $views = array();

        foreach ($variables['form'] as $child) {
            $views[] = $this->renderer->searchAndRenderBlock($child, 'widget');
        }

        return $views;
    }

    /*
        <select {{ block('widget_attributes') }}{% if multiple %} multiple="multiple"{% endif %}>
            {% if empty_value is not none %}
                <option value="">{{ empty_value|trans({}, translation_domain) }}</option>
            {% endif %}
            {% if preferred_choices|length > 0 %}
                {% set options = preferred_choices %}
                {{ block('choice_widget_options') }}
                {% if choices|length > 0 and separator is not none %}
                    <option disabled="disabled">{{ separator }}</option>
                {% endif %}
            {% endif %}
            {% set options = choices %}
            {{ block('choice_widget_options') }}
        </select>
    */
    protected function renderChoiceWidgetCollapsed(FormView $view, $blockName, array $variables = array())
    {
        $select = new FormRepresentations\Select();
        $this->addWidgetAttributes($select, $view, $blockName, $variables);
        if ($variables['multiple']) {
            $select['attributes']['multiple'] = 'multiple';
        }

        if (null !== $variables['empty_value']) {
            $noneOption = new FormRepresentations\Option();
            $noneOption['attributes']['value'] = '';
            $noneOption->value = $variables['empty_value'];

            $select->options[] = $noneOption;
        }

        if (0 < count($variables['preferred_choices'])) {
            $select->options = array_merge($select->options, $this->renderChoiceWidgetOptions($view, $blockName, array_merge($variables, array(
                'options' => $variables['preferred_choices'],
            ))));

            if (0 < count($variables['choices']) && null !== $variables['separator']) {
                $separatorOption = new FormRepresentations\Option();
                $separatorOption->attributes['disabled'] = 'disabled';
                $separatorOption->value = $variables['separator'];

                $select->options[] = $separatorOption;
            }
        }

        $select->options = array_merge($select->options, $this->renderChoiceWidgetOptions($view, $blockName, array_merge($variables, array(
            'options' => $variables['choices'],
        ))));

        return $select;
    }

    /*
        {% for group_label, choice in options %}
            {% if choice is iterable %}
                <optgroup label="{{ group_label|trans({}, translation_domain) }}">
                    {% set options = choice %}
                    {{ block('choice_widget_options') }}
                </optgroup>
            {% else %}
                <option value="{{ choice.value }}"{% if choice is selectedchoice(value) %} selected="selected"{% endif %}>{{ choice.label|trans({}, translation_domain) }}</option>
            {% endif %}
        {% endfor %}
    */
    protected function renderChoiceWidgetOptions(FormView $view, $blockName, array $variables = array())
    {
        $options = array();
        $valuePropertyPath = new PropertyPath('value');
        $labelPropertyPath = new PropertyPath('label');

        foreach ($variables['options'] as $groupLabel => $choice) {
            if (is_array($choice) || $choice instanceof \Traversable) {
                $options = array_merge($options, $this->renderChoiceWidgetOptions($view, $blockName, array_merge($variables, array(
                    'options' => $choice,
                ))));
            } else {
                $option = new FormRepresentations\Option();
                $option->attributes['value'] = $valuePropertyPath->getValue($choice);
                if ($this->isSelectedChoice($choice, $variables['value'])) {
                    $option['attributes']['selected'] = 'selected';
                }
                $option->value = $labelPropertyPath->getValue($choice);

                $options[] = $option;
            }
        }

        return $options;
    }

    /*
        {% for child in form %}
            {{ form_row(child) }}
        {% endfor %}
    */
    protected function renderFormRows(FormView $view, $blockName, array $variables = array())
    {
        $rows = array();

        foreach ($variables['form'] as $child) {
            $rows[] = $this->renderFormRow($child, $blockName, $variables);
        }

        return $rows;
    }

    /*
        id="{{ id }}"
        name="{{ full_name }}"
        {% if read_only %} readonly="readonly"{% endif %}
        {% if disabled %} disabled="disabled"{% endif %}
        {% if required %} required="required"{% endif %}
        {% if max_length %} maxlength="{{ max_length }}"{% endif %}
        {% if pattern %} pattern="{{ pattern }}"{% endif %}
        {% for attrname, attrvalue in attr %}
            {% if attrname in ['placeholder', 'title'] %}
                {{ attrname }}="{{ attrvalue|trans({}, translation_domain) }}"
            {% else %}
                {{ attrname }}="{{ attrvalue }}"
            {% endif %}
        {% endfor %}
     */
    protected function addWidgetAttributes($widget, FormView $view, $blockName, array $variables = array())
    {
        $widget->attributes['name'] = $variables['full_name'];

        if ($variables['read_only']) {
            $widget->attributes['readonly'] = 'readonly';
        }

        if ($variables['disabled']) {
            $widget->attributes['disabled'] = 'disabled';
        }

        if ($variables['required']) {
            $widget->attributes['required'] = 'required';
        }

        if ($variables['max_length']) {
            $widget->attributes['maxlength'] = $variables['max_length'];
        }

        if ($variables['pattern']) {
            $widget->attributes['pattern'] = $variables['pattern'];
        }

        foreach ($variables['attr'] as $attrname => $attrvalue) {
            if (in_array($attrname, array('placeholder', 'title'))) {
                // TODO Translate the thing ...
            }

            $widget->attributes[$attrname] = $attrvalue;
        }
    }

    /**
     * Pasted from the symfony src code
     *
     * @see Symfony\Bridge\Twig\Extension\FormExtension::isSelectedChoice
     */
    protected function isSelectedChoice(ChoiceView $choice, $selectedValue)
    {
        if (is_array($selectedValue)) {
            return false !== array_search($choice->value, $selectedValue, true);
        }

        return $choice->value === $selectedValue;
    }
}
