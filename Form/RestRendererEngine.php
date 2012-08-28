<?php

namespace FSC\Common\RestBundle\Form;

use Symfony\Component\Form\AbstractRendererEngine;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormRendererInterface;

use FSC\Common\RestBundle\Model\Representation\Form;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class RestRendererEngine extends AbstractRendererEngine
{
    /**
     * @var RestFormRenderer
     */
    public $renderer;

    /**
     * {@inheritdoc}
     */
    protected function loadResourceForBlockName($cacheKey, FormView $view, $blockName)
    {
        $this->resources[$cacheKey][$blockName] = true;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function renderBlock(FormView $view, $resource, $blockName, array $variables = array())
    {
        preg_match('/_([^_]+)$/', $blockName, $matches);
        $blockNameSuffix = $matches[1];
        $widgetName = substr($variables['cache_key'], strlen($variables['unique_block_prefix']) + 1);

        $renderedView = null;

        if ('rest' == $blockNameSuffix) {
            return $this->renderFormRest($view, $resource, $blockName, $variables);
        }

        switch ($widgetName) {
            // Text fields
            case 'textarea': return $this->renderTextareaWidget($view, $resource, $blockName, $variables);
            case 'email': return $this->renderEmailWidget($view, $resource, $blockName, $variables);
            case 'integer': return $this->renderIntegerWidget($view, $resource, $blockName, $variables);
            case 'money': return $this->renderMoneyWidget($view, $resource, $blockName, $variables);
            case 'number': return $this->renderNumberWidget($view, $resource, $blockName, $variables);
            case 'password': return $this->renderPasswordWidget($view, $resource, $blockName, $variables);
            case 'percent': return $this->renderPercentWidget($view, $resource, $blockName, $variables);
            case 'search': return $this->renderSearchWidget($view, $resource, $blockName, $variables);
            case 'url': return $this->renderUrlWidget($view, $resource, $blockName, $variables);
            case 'hidden': return $this->renderHiddenWidget($view, $resource, $blockName, $variables);

            // Choice fields
        }

        switch ($blockNameSuffix) {
            case 'widget': return $this->renderFormWidget($view, $resource, $blockName, $variables);
            case 'row': return $this->renderFormRow($view, $resource, $blockName, $variables);
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
    protected function renderFormWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        return $variables['compound']
            ? $this->renderWidgetCompound($view, $resource, $blockName, $variables)
            : $this->renderWidgetSimple($view, $resource, $blockName, $variables)
            ;
    }

    /*
        {% set type = type|default('text') %}
        <input type="{{ type }}" {{ block('widget_attributes') }} {% if value is not empty %}value="{{ value }}" {% endif %}/>
     */
    protected function renderWidgetSimple(FormView $view, $resource, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'text';

        $input = new Form\Input();
        $input->attributes['type'] = $variables['type'];

        if (!empty($variables['value'])) {
            $input->attributes['value'] = $variables['value'];
        }

        $this->addWidgetAttributes($input, $view, $resource, $blockName, $variables);

        return $input;
    }

    protected function renderWidgetCompound(FormView $view, $resource, $blockName, array $variables = array())
    {
        throw new \Exception(__METHOD__.' not implemented');
    }

    /*
        <div>
            {{ form_label(form) }}
            {{ form_errors(form) }}
            {{ form_widget(form) }}
        </div>
     */
    protected function renderFormRow(FormView $view, $resource, $blockName, array $variables = array())
    {
        return $this->renderer->searchAndRenderBlock($view, 'widget');
    }

    /*
        {% for child in form %}
            {% if not child.rendered %}
                {{ form_row(child) }}
            {% endif %}
        {% endfor %}
     */
    protected function renderFormRest(FormView $view, $resource, $blockName, array $variables = array())
    {
        $renderedView = array();

        // TODO, here add the rendered views to a form object, or something

        foreach ($view->getChildren() as $childView) {
            $renderedView[] = $this->renderer->searchAndRenderBlock($childView, 'row');
        }

        return $renderedView;
    }

    /*
        <textarea {{ block('widget_attributes') }}>{{ value }}</textarea>
     */
    protected function renderTextareaWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        $widget = new Form\Textarea();

        $widget->value = $variables['value'];

        $this->addWidgetAttributes($widget, $view, $resource, $blockName, $variables);

        return $widget;
    }

    /*
        {% set type = type|default('email') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderEmailWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'email';

        return $this->renderWidgetSimple($view, $resource, $blockName, $variables);
    }

    /*
        {% set type = type|default('number') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderIntegerWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'integer';

        return $this->renderWidgetSimple($view, $resource, $blockName, $variables);
    }

    /*
        {{ money_pattern|replace({ '{{ widget }}': block('form_widget_simple') })|raw }}
    */
    protected function renderMoneyWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        throw new \Exception(__METHOD__.' not implemented');
    }

    /*
        {# type="number" doesn't work with floats #}
        {% set type = type|default('text') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderNumberWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'number';

        return $this->renderWidgetSimple($view, $resource, $blockName, $variables);
    }

    /*
        {% set type = type|default('password') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderPasswordWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'password';

        return $this->renderWidgetSimple($view, $resource, $blockName, $variables);
    }

    /*
        {% set type = type|default('text') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderPercentWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'text';

        return $this->renderWidgetSimple($view, $resource, $blockName, $variables);
    }

    /*
        {% set type = type|default('search') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderSearchWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'search';

        return $this->renderWidgetSimple($view, $resource, $blockName, $variables);
    }

    /*
        {% set type = type|default('url') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderUrlWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'url';

        return $this->renderWidgetSimple($view, $resource, $blockName, $variables);
    }

    /*
        {% set type = type|default('hidden') %}
        {{ block('form_widget_simple') }}
    */
    protected function renderHiddenWidget(FormView $view, $resource, $blockName, array $variables = array())
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'hidden';

        return $this->renderWidgetSimple($view, $resource, $blockName, $variables);
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
    protected function addWidgetAttributes($widget, FormView $view, $resource, $blockName, array $variables = array())
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

    public function setRenderer(RestFormRenderer $renderer)
    {
        $this->renderer = $renderer;
    }
}
