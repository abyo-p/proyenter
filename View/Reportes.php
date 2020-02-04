{#
   
#}


{% block body %}
    {# -- Calculate texts according to language -- #}
    {% set title = i18n.trans(fsc.getPageData()['title']) | capitalize %}
    {% set generate, generate_title = i18n.trans('generate'), i18n.trans('generate-title') %}

    {# -- Others Values -- #}
    {% set exportOptions = fsc.exportManager.options() %}

    {# -- Main Body -- #}
    <div class="container-fluid d-print-none">
        {{ parent() }}

        {# -- Header Row -- #}
        <div class="row">
            <div class="col-sm-7 col-6">
                <div class="btn-group d-xs-none">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ py.url() }}" title="{{ i18n.trans('refresh') }}">
                        <i class="fas fa-redo" aria-hidden="true"></i>
                    </a>
                    {% if fsc.getPageData()['name'] == fsc.user.homepage %}
                        <a class="btn btn-sm btn-outline-secondary active" href="{{ fsc.url() }}?defaultPage=FALSE" title="{{ i18n.trans('marked-as-homepage') }}">
                            <i class="fas fa-bookmark" aria-hidden="true"></i>
                        </a>
                    {% else %}
                        <a class="btn btn-sm btn-outline-secondary" href="{{ fsc.url() }}?defaultPage=TRUE" title="{{ i18n.trans('marked-as-homepage') }}">
                            <i class="far fa-bookmark" aria-hidden="true"></i>
                        </a>
                    {% endif %}
                </div>
            </div>
            <div class="col-sm-5 col-6 text-right">
                <h1 class="h3">
                    <i class="{{ py.getPageData()['icon'] }}" aria-hidden="true"></i> {{ title }}
                </h1>
            </div>
        </div>

        {# -- Data Row -- #}
        <div class="row">
            {# -- Left Panel -- #}
            <div class="col-sm-2">
                <div class="nav flex-column nav-pills" id="mainTabs" role="tablist" aria-orientation="vertical">
                    {% for ejercicio in fsc.ejercicios %}
                        <a class="nav-link{% if loop.first %} active{% endif %}" id="tab-{{ ejercicio.codejercicio }}" data-toggle="pill" href="#data-{{ ejercicio.codejercicio }}" role="tab" aria-controls="data-{{ ejercicio.codejercicio }}" aria-selected="{{ loop.first }}">
                            <i class="fas fa-calendar-alt fa-fw" aria-hidden="true"></i> {{ ejercicio.nombre }}
                        </a>
                    {% endfor %}
                </div>
            </div>

            {# -- Right Panel -- #}
            <div class="col-sm-10">
                <div class="tab-content" id="mainTabsContent">
                    {% for ejercicio in py.ejercicios %}
                                <div class="card">
                                <div class="card-body">
                                    {% for action, report in fsc.getReports() %}
                                        <form action="{{ fsc.url() }}" method="post">
                                            <input type="hidden" name="action" value="{{ action }}"/>
                                            <input type="hidden" name="codejercicio" value="{{ ejercicio.codejercicio }}"/>

                                            <div class="row">
                                                {# -- Document Title -- #}
                                                <div class="col-12">
                                                    <h3 class="h4 text-capitalize">
                                                        <i class="fas fa-book fa-fw" aria-hidden="true"></i> {{ i18n.trans(report.description) }}
                                                    </h3>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="form-group">
                                                        <input type="text" name="date-from" value="{{ ejercicio.fechainicio }}" class="form-control datepicker" autocomplete="off"/>
                                                    </div>
                                                </div>

                                                <div class="col-2">
                                                    <div class="form-group">
                                                        <input type="text" name="date-to" value="{{ ejercicio.fechafin }}" class="form-control datepicker" autocomplete="off"/>
                                                    </div>
                                                </div>

                                                {% if report.grouping %}
                                                    <div class="col-2">
                                                        <div class="form-group">
                                                            <select name="grouping" class="form-control">
                                                                <option value="YES">{{ i18n.trans('report-grouping-account') }}</option>
                                                                <option value="NO">{{ i18n.trans('report-non-grouping-account') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                {% endif %}

                                                <div class="col-2">
                                                    <div class="form-group">
                                                        <select name="format" class="form-control">
                                                            {% for key, option in exportOptions %}
                                                                <option value="{{ key }}">{{ i18n.trans(option['description']) }}</option>
                                                            {% endfor %}
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <button type="submit" class="btn btn-primary" title="{{ generate_title }}">
                                                        <i class="fas fa-eye fa-fw" aria-hidden="true"></i> {{ generate }}
                                                    </button>
                                                </div>
                                            </div>

                                            {% if not loop.last %}<hr />{% endif %}
                                        </form>
                                    {% endfor %}
                                </div>
                            </div>
                        </div>
                    {% endfor %}
                </div>
            </div>
        </div>
    </div>
{% endblock %}
