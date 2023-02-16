<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Controller;

use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor;
use Nelmio\ApiDocBundle\Formatter\HtmlFormatter;
use Nelmio\ApiDocBundle\Formatter\RequestAwareSwaggerFormatter;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiDocController extends AbstractController
{
    private ApiDocExtractor $apiDocExtractor;
    private HtmlFormatter $htmlFormatter;

    public function __construct(ApiDocExtractor $apiDocExtractor, HtmlFormatter $htmlFormatter)
    {
        $this->apiDocExtractor = $apiDocExtractor;
        $this->htmlFormatter = $htmlFormatter;
    }

    public function indexAction($view = ApiDoc::DEFAULT_VIEW)
    {
        $extractedDoc = $this->apiDocExtractor->all($view);
        $htmlContent  = $this->htmlFormatter->format($extractedDoc);

        return new Response($htmlContent, 200, array('Content-Type' => 'text/html'));
    }

    public function swaggerAction(Request $request, $resource = null)
    {

        $docs = $this->get('nelmio_api_doc.extractor.api_doc_extractor')->all();
        $formatter = new RequestAwareSwaggerFormatter($request, $this->get('nelmio_api_doc.formatter.swagger_formatter'));

        $spec = $formatter->format($docs, $resource ? '/' . $resource : null);

        if ($resource !== null && count($spec['apis']) === 0) {
            throw $this->createNotFoundException(sprintf('Cannot find resource "%s"', $resource));
        }

        return new JsonResponse($spec);
    }
}
