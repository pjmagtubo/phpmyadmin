<?php
/**
 * Holds the PhpMyAdmin\Controllers\Server\Status\MonitorController
 */

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Server\Status;

use PhpMyAdmin\Common;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Response;
use PhpMyAdmin\Server\Status\Data;
use PhpMyAdmin\Server\Status\Monitor;
use PhpMyAdmin\Server\SysInfo\SysInfo;
use PhpMyAdmin\Template;
use function is_numeric;
use function microtime;

class MonitorController extends AbstractController
{
    /** @var Monitor */
    private $monitor;

    /** @var DatabaseInterface */
    private $dbi;

    /**
     * @param Response          $response
     * @param Data              $data
     * @param Monitor           $monitor
     * @param DatabaseInterface $dbi
     */
    public function __construct($response, Template $template, $data, $monitor, $dbi)
    {
        parent::__construct($response, $template, $data);
        $this->monitor = $monitor;
        $this->dbi = $dbi;
    }

    public function index(): void
    {
        global $PMA_Theme;

        Common::server();

        $this->addScriptFiles([
            'vendor/jquery/jquery.tablesorter.js',
            'vendor/jquery/jquery.sortableTable.js',
            'vendor/jqplot/jquery.jqplot.js',
            'vendor/jqplot/plugins/jqplot.pieRenderer.js',
            'vendor/jqplot/plugins/jqplot.enhancedPieLegendRenderer.js',
            'vendor/jqplot/plugins/jqplot.canvasTextRenderer.js',
            'vendor/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js',
            'vendor/jqplot/plugins/jqplot.dateAxisRenderer.js',
            'vendor/jqplot/plugins/jqplot.highlighter.js',
            'vendor/jqplot/plugins/jqplot.cursor.js',
            'jqplot/plugins/jqplot.byteFormatter.js',
            'server/status/monitor.js',
            'server/status/sorter.js',
        ]);

        $form = [
            'server_time' => (int) (microtime(true) * 1000),
            'server_os' => SysInfo::getOs(),
            'is_superuser' => $this->dbi->isSuperuser(),
            'server_db_isLocal' => $this->data->dbIsLocal,
        ];

        $javascriptVariableNames = [];
        foreach ($this->data->status as $name => $value) {
            if (! is_numeric($value)) {
                continue;
            }

            $javascriptVariableNames[] = $name;
        }

        $this->render('server/status/monitor/index', [
            'image_path' => $PMA_Theme->getImgPath(),
            'javascript_variable_names' => $javascriptVariableNames,
            'form' => $form,
        ]);
    }

    public function chartingData(): void
    {
        $params = ['requiredData' => $_POST['requiredData'] ?? null];

        Common::server();

        if (! $this->response->isAjax()) {
            return;
        }

        $this->response->addJSON([
            'message' => $this->monitor->getJsonForChartingData(
                $params['requiredData'] ?? ''
            ),
        ]);
    }

    public function logDataTypeSlow(): void
    {
        $params = [
            'time_start' => $_POST['time_start'] ?? null,
            'time_end' => $_POST['time_end'] ?? null,
        ];

        Common::server();

        if (! $this->response->isAjax()) {
            return;
        }

        $this->response->addJSON([
            'message' => $this->monitor->getJsonForLogDataTypeSlow(
                (int) $params['time_start'],
                (int) $params['time_end']
            ),
        ]);
    }

    public function logDataTypeGeneral(): void
    {
        $params = [
            'time_start' => $_POST['time_start'] ?? null,
            'time_end' => $_POST['time_end'] ?? null,
            'limitTypes' => $_POST['limitTypes'] ?? null,
            'removeVariables' => $_POST['removeVariables'] ?? null,
        ];

        Common::server();

        if (! $this->response->isAjax()) {
            return;
        }

        $this->response->addJSON([
            'message' => $this->monitor->getJsonForLogDataTypeGeneral(
                (int) $params['time_start'],
                (int) $params['time_end'],
                (bool) $params['limitTypes'],
                (bool) $params['removeVariables']
            ),
        ]);
    }

    public function loggingVars(): void
    {
        $params = [
            'varName' => $_POST['varName'] ?? null,
            'varValue' => $_POST['varValue'] ?? null,
        ];

        Common::server();

        if (! $this->response->isAjax()) {
            return;
        }

        $this->response->addJSON([
            'message' => $this->monitor->getJsonForLoggingVars(
                $params['varName'],
                $params['varValue']
            ),
        ]);
    }

    public function queryAnalyzer(): void
    {
        $params = [
            'database' => $_POST['database'] ?? null,
            'query' => $_POST['query'] ?? null,
        ];

        Common::server();

        if (! $this->response->isAjax()) {
            return;
        }

        $this->response->addJSON([
            'message' => $this->monitor->getJsonForQueryAnalyzer(
                $params['database'] ?? '',
                $params['query'] ?? ''
            ),
        ]);
    }
}
