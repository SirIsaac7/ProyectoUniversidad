<?php

return [
    'tipos' => [
        'resumen_general' => 'Resumen general del sistema',
        'proveedores' => 'Proveedores y verificacion',
        'solicitudes_citas' => 'Solicitudes y citas',
        'calificaciones' => 'Calificaciones y respuestas',
        'documentos' => 'Documentos de proveedores',
        'backups' => 'Backups del sistema',
        'activity_logs' => 'Activity Logs / auditoria',
    ],

    'tamano_hoja' => [
        'letter' => 'Carta',
        'a4' => 'A4',
        'legal' => 'Legal',
    ],

    'orientaciones' => [
        'portrait' => 'Vertical',
        'landscape' => 'Horizontal',
    ],

    'opciones' => [
        'resumen_general' => [
            'switches' => [
                'incluir_usuarios' => 'Usuarios',
                'incluir_proveedores' => 'Proveedores',
                'incluir_solicitudes' => 'Solicitudes',
                'incluir_citas' => 'Citas',
                'incluir_calificaciones' => 'Calificaciones',
                'incluir_documentos' => 'Documentos',
            ],
        ],
        'proveedores' => [
            'selects' => [
                'estado_verificacion' => [
                    'label' => 'Estado de verificacion',
                    'opciones' => [
                        'todos' => 'Todos',
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ],
                ],
            ],
            'switches' => [
                'solo_con_ubicacion' => 'Solo proveedores con ubicacion',
                'solo_con_documentos' => 'Solo proveedores con documentos',
            ],
        ],
        'solicitudes_citas' => [
            'selects' => [
                'estado_solicitud' => [
                    'label' => 'Estado de solicitud',
                    'opciones' => [
                        'todos' => 'Todos',
                        'pendiente' => 'Pendiente',
                        'aceptada' => 'Aceptada',
                        'rechazada' => 'Rechazada',
                        'cancelada' => 'Cancelada',
                        'finalizada' => 'Finalizada',
                    ],
                ],
                'estado_cita' => [
                    'label' => 'Estado de cita',
                    'opciones' => [
                        'todos' => 'Todos',
                        'programada' => 'Programada',
                        'en_atencion' => 'En atencion',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                        'no_asistio' => 'No asistio',
                        'vencida' => 'Vencida',
                    ],
                ],
            ],
            'switches' => [
                'incluir_solicitudes' => 'Incluir resumen de solicitudes',
                'incluir_citas' => 'Incluir detalle de citas',
            ],
        ],
        'calificaciones' => [
            'selects' => [
                'estado_calificacion' => [
                    'label' => 'Estado de calificacion',
                    'opciones' => [
                        'todos' => 'Todos',
                        'visible' => 'Visible',
                        'oculta' => 'Oculta',
                        'pendiente_revision' => 'Pendiente de revision',
                    ],
                ],
                'puntuacion_minima' => [
                    'label' => 'Puntuacion minima',
                    'opciones' => [
                        'todas' => 'Todas',
                        '5' => '5 estrellas',
                        '4' => '4 estrellas o mas',
                        '3' => '3 estrellas o mas',
                        '2' => '2 estrellas o mas',
                        '1' => '1 estrella o mas',
                    ],
                ],
            ],
            'switches' => [
                'incluir_respuestas' => 'Incluir respuestas del proveedor',
                'incluir_criterios' => 'Incluir criterios evaluados',
            ],
        ],
        'documentos' => [
            'selects' => [
                'estado_revision' => [
                    'label' => 'Estado de revision',
                    'opciones' => [
                        'todos' => 'Todos',
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ],
                ],
            ],
            'switches' => [
                'solo_con_archivo' => 'Solo documentos con archivo',
            ],
        ],
        'backups' => [
            'switches' => [
                'incluir_configuracion' => 'Incluir configuracion automatica',
                'incluir_archivos' => 'Incluir archivos locales',
            ],
        ],
        'activity_logs' => [
            'selects' => [
                'actor' => [
                    'label' => 'Actor',
                    'opciones' => [
                        'todos' => 'Todos',
                        'usuarios' => 'Solo usuarios',
                        'sistema' => 'Solo sistema',
                    ],
                ],
            ],
            'switches' => [
                'incluir_propiedades' => 'Incluir propiedades del cambio',
            ],
        ],
    ],
];
