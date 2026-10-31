<?php

class PageController extends Controller
{
    private PublicPageRepository $repo;

    public function __construct()
    {
        $this->repo = new PublicPageRepository();
    }


    /* ============================================================
       LISTADO
       ============================================================ */

    public function index(): void
    {
        $this->view(
            'admin/module-layout',
            [
                'contentView' =>
                    __DIR__ . '/../Views/admin/pages.php'
            ]
        );
    }


    /* ============================================================
       CREAR
       ============================================================ */

    public function create(): void
    {
        $this->view(
            'admin/module-layout',
            [
                'contentView' =>
                    __DIR__ . '/../Views/admin/page-form.php',

                'adminPage' => null
            ]
        );
    }


    /* ============================================================
       EDITAR
       ============================================================ */

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);


        if ($id <= 0) {

            http_response_code(404);

            echo 'Página no encontrada.';

            return;
        }


        $page =
            $this->repo->findById($id);


        if (!$page) {

            http_response_code(404);

            echo 'Página no encontrada.';

            return;
        }


        $this->view(
            'admin/module-layout',
            [
                'contentView' =>
                    __DIR__ . '/../Views/admin/page-form.php',

                'adminPage' => $page
            ]
        );
    }


    /* ============================================================
       DATA
       ============================================================ */

    public function data(): void
    {
        try {

            $pages =
                $this->repo->all();


            $this->json(
                [
                    'success' => true,
                    'data' => $pages
                ]
            );


        } catch (Throwable $e) {

            error_log(
                '[PAGE DATA] ' .
                $e->getMessage()
            );


            $this->json(
                [
                    'success' => false,
                    'message' =>
                        'No fue posible cargar las páginas.'
                ],
                500
            );
        }
    }


    /* ============================================================
       GUARDAR
       ============================================================ */

    public function save(): void
    {
        try {

            $d =
                $this->getJsonInput();


            /* ----------------------------------------------------
               DATOS
            ---------------------------------------------------- */

            $id =
                (int)($d['id'] ?? 0);


            $title =
                trim(
                    (string)($d['title'] ?? '')
                );


            $slug =
                trim(
                    strtolower(
                        (string)($d['slug'] ?? '')
                    )
                );


            $type =
                trim(
                    (string)(
                        $d['page_type']
                        ?? 'general'
                    )
                );


            $h1 =
                trim(
                    (string)($d['h1'] ?? '')
                );


            $metaTitle =
                trim(
                    (string)(
                        $d['meta_title']
                        ?? ''
                    )
                );


            $metaDescription =
                trim(
                    (string)(
                        $d['meta_description']
                        ?? ''
                    )
                );


            $html =
                $this->sanitizeHtml(
                    (string)(
                        $d['content_html']
                        ?? ''
                    )
                );


            $published =
                !empty(
                $d['is_published']
                )
                    ? 1
                    : 0;


            /* ----------------------------------------------------
               VALIDAR TÍTULO
            ---------------------------------------------------- */

            if (
                $title === '' ||
                mb_strlen($title) > 180
            ) {

                $this->json(
                    [
                        'success' => false,
                        'message' =>
                            'El título es obligatorio y debe tener máximo 180 caracteres.'
                    ],
                    422
                );

                return;
            }


            /* ----------------------------------------------------
               VALIDAR SLUG
            ---------------------------------------------------- */

            if (
                $slug === '' ||
                mb_strlen($slug) > 180 ||
                !preg_match(
                    '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    $slug
                )
            ) {

                $this->json(
                    [
                        'success' => false,
                        'message' =>
                            'El slug solo puede contener letras minúsculas, números y guiones.'
                    ],
                    422
                );

                return;
            }


            /* ----------------------------------------------------
               TIPOS PERMITIDOS

               IMPORTANTE:
               La BD permite:
               general
               legal
               contact
               event
            ---------------------------------------------------- */

            $allowedTypes = [
                'general',
                'legal',
                'contact',
                'event'
            ];


            if (
                !in_array(
                    $type,
                    $allowedTypes,
                    true
                )
            ) {

                $type = 'general';
            }


            /* ----------------------------------------------------
               VALORES POR DEFECTO
            ---------------------------------------------------- */

            if ($metaTitle === '') {

                $metaTitle =
                    $title;
            }


            if ($h1 === '') {

                $h1 =
                    $title;
            }


            /* ----------------------------------------------------
               GUARDAR
            ---------------------------------------------------- */

            $saved =
                $this->repo->save(
                    [
                        'id' =>
                            $id,

                        'slug' =>
                            $slug,

                        'page_type' =>
                            $type,

                        'title' =>
                            $title,

                        'meta_title' =>
                            $metaTitle,

                        'meta_description' =>
                            $metaDescription,

                        'h1' =>
                            $h1,

                        'content_html' =>
                            $html,

                        'is_published' =>
                            $published
                    ]
                );


            /* ----------------------------------------------------
               RESPUESTA
            ---------------------------------------------------- */

            $this->json(
                [
                    'success' => true,

                    'message' =>
                        'Página guardada correctamente.',

                    'id' =>
                        $saved
                ]
            );


        } catch (PDOException $e) {

            error_log(
                '[PAGE SAVE] ' .
                $e->getMessage()
            );


            $message =
                strtolower(
                    $e->getMessage()
                );


            if (
                str_contains(
                    $message,
                    'duplicate'
                ) ||
                str_contains(
                    $message,
                    'unique'
                )
            ) {

                $errorMessage =
                    'Ese slug ya está en uso.';

            } else {

                $errorMessage =
                    'No fue posible guardar la página.';
            }


            $this->json(
                [
                    'success' => false,
                    'message' => $errorMessage
                ],
                422
            );


        } catch (Throwable $e) {

            error_log(
                '[PAGE SAVE] ' .
                $e->getMessage()
            );


            $this->json(
                [
                    'success' => false,
                    'message' =>
                        'No fue posible guardar la página.'
                ],
                500
            );
        }
    }


    /* ============================================================
       CAMBIAR ESTADO
       ============================================================ */

    public function toggle(): void
    {
        try {

            $d =
                $this->getJsonInput();


            $id =
                (int)($d['id'] ?? 0);


            $published =
                !empty(
                $d['is_published']
                )
                    ? 1
                    : 0;


            if ($id <= 0) {

                $this->json(
                    [
                        'success' => false,
                        'message' =>
                            'ID de página inválido.'
                    ],
                    422
                );

                return;
            }


            $this->repo->toggle(
                $id,
                $published
            );


            $this->json(
                [
                    'success' => true,
                    'message' =>
                        'Estado actualizado correctamente.'
                ]
            );


        } catch (Throwable $e) {

            error_log(
                '[PAGE TOGGLE] ' .
                $e->getMessage()
            );


            $this->json(
                [
                    'success' => false,
                    'message' =>
                        'No fue posible cambiar el estado.'
                ],
                500
            );
        }
    }


    /* ============================================================
       SANITIZAR HTML
       ============================================================ */

    private function sanitizeHtml(
        string $html
    ): string
    {
        if (
            trim($html) === ''
        ) {

            return '';
        }


        /*
         * Etiquetas permitidas
         */

        $allowed = [

            'p',
            'br',

            'strong',
            'b',

            'em',
            'i',

            'u',
            's',

            'ul',
            'ol',
            'li',

            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',

            'blockquote',

            'div',
            'span',

            'a',

            'table',
            'thead',
            'tbody',
            'tr',
            'th',
            'td'
        ];


        $dom =
            new DOMDocument(
                '1.0',
                'UTF-8'
            );


        libxml_use_internal_errors(
            true
        );


        /*
         * Cargar HTML dentro de un
         * contenedor controlado.
         */

        $dom->loadHTML(
            '<?xml encoding="UTF-8">' .
            '<div id="root">' .
            $html .
            '</div>',
            LIBXML_HTML_NOIMPLIED |
            LIBXML_HTML_NODEFDTD
        );


        $root =
            $dom->getElementById(
                'root'
            );


        if (!$root) {

            libxml_clear_errors();

            return '';
        }


        /*
         * Recorrer y limpiar nodos
         */

        $walk =
            function (
                DOMNode $node
            ) use (
                &$walk,
                $allowed
            ) {

                for (
                    $i =
                        $node->childNodes->length - 1;

                    $i >= 0;

                    $i--
                ) {

                    $child =
                        $node->childNodes->item(
                            $i
                        );


                    /*
                     * Solo procesamos
                     * elementos HTML.
                     */

                    if (
                        $child->nodeType !==
                        XML_ELEMENT_NODE
                    ) {

                        continue;
                    }


                    $tag =
                        strtolower(
                            $child->nodeName
                        );


                    /*
                     * Eliminar etiquetas
                     * no permitidas.
                     */

                    if (
                        !in_array(
                            $tag,
                            $allowed,
                            true
                        )
                    ) {

                        while (
                        $child->firstChild
                        ) {

                            $node->insertBefore(
                                $child->firstChild,
                                $child
                            );
                        }


                        $node->removeChild(
                            $child
                        );

                        continue;
                    }


                    /*
                     * Limpiar atributos
                     */

                    for (
                        $j =
                            $child->attributes->length - 1;

                        $j >= 0;

                        $j--
                    ) {

                        $attribute =
                            $child->attributes->item(
                                $j
                            );


                        $name =
                            strtolower(
                                $attribute->name
                            );


                        $value =
                            $attribute->value;


                        /*
                         * Atributos permitidos
                         */

                        $allowedAttribute =
                            in_array(
                                $name,
                                [
                                    'style',
                                    'href',
                                    'target',
                                    'rel',
                                    'class'
                                ],
                                true
                            );


                        if (
                            !$allowedAttribute
                        ) {

                            $child->removeAttribute(
                                $name
                            );

                            continue;
                        }


                        /*
                         * Validar href
                         */

                        if (
                            $name === 'href' &&
                            !preg_match(
                                '/^(https?:|mailto:|\/)/i',
                                $value
                            )
                        ) {

                            $child->removeAttribute(
                                $name
                            );

                            continue;
                        }


                        /*
                         * Bloquear JavaScript
                         * dentro de estilos.
                         */

                        if (
                            $name === 'style' &&
                            preg_match(
                                '/javascript\s*:|expression\s*\(|url\s*\(/i',
                                $value
                            )
                        ) {

                            $child->removeAttribute(
                                $name
                            );

                            continue;
                        }
                    }


                    /*
                     * Los enlaces siempre
                     * se abren de forma segura.
                     */

                    if (
                        $tag === 'a'
                    ) {

                        $child->setAttribute(
                            'rel',
                            'noopener noreferrer'
                        );


                        $child->setAttribute(
                            'target',
                            '_blank'
                        );
                    }


                    /*
                     * Continuar recorriendo
                     * los hijos.
                     */

                    $walk(
                        $child
                    );
                }
            };


        $walk(
            $root
        );


        /*
         * Reconstruir HTML limpio.
         */

        $output = '';


        foreach (
            $root->childNodes as $node
        ) {

            $output .=
                $dom->saveHTML(
                    $node
                );
        }


        libxml_clear_errors();


        return $output;
    }


    /* ============================================================
       JSON INPUT
       ============================================================ */

    private function getJsonInput(): array
    {
        $raw =
            file_get_contents(
                'php://input'
            );


        if (
            $raw === false ||
            trim($raw) === ''
        ) {

            return [];
        }


        $data =
            json_decode(
                $raw,
                true
            );


        return
            is_array($data)
                ? $data
                : [];
    }
}