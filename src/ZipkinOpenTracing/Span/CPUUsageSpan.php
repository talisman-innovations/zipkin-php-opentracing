<?php

namespace ZipkinOpenTracing\Span;

use Throwable;
use Zipkin\Endpoint;
use Zipkin\Propagation\TraceContext;
use Zipkin\Span as Span;

class CPUUsageSpan implements Span
{
    private Span $span;
    private array $usageStatsStart = [];

    public function __construct(Span $span)
    {
        $this->span = $span;
    }

    public function finish($timestamp = null): void
    {
        $startUsage = $this->usageStatsStart;

        if (!empty($startUsage)) {
            $userCpuUsageStart = $startUsage["ru_utime.tv_usec"];
            $userCpuUsageEnd = getrusage()["ru_utime.tv_usec"];
            $systemCpuUsageStart = $startUsage["ru_stime.tv_usec"];
            $systemCpuUsageEnd = getrusage()["ru_stime.tv_usec"];

            $userTime = (float)($userCpuUsageEnd - $userCpuUsageStart) / 1e+6; // Convert to seconds
            $systemTime = (float)($systemCpuUsageEnd - $systemCpuUsageStart) / 1e+6;

            $this->tag('cpu.user', $userTime);
            $this->tag('cpu.system', $systemTime);
        }

        $this->span->finish($timestamp);
    }

    public function isNoop(): bool
    {
        return $this->span->isNoop();
    }

    public function getContext(): TraceContext
    {
        return $this->span->getContext();
    }

    public function start(int $timestamp = null): void
    {
        $this->span->start($timestamp);
        $this->usageStatsStart = getrusage();
    }

    public function setName(string $name): void
    {
        $this->span->setName($name);
    }

    public function setKind(string $kind): void
    {
        $this->span->setKind($kind);
    }

    public function tag(string $key, string $value): void
    {
        $this->span->tag($key, $value);
    }

    public function setError(Throwable $e): void
    {
        $this->span->setError($e);
    }

    public function annotate(string $value, int $timestamp = null): void
    {
        $this->span->annotate($value, $timestamp);
    }

    public function setRemoteEndpoint(Endpoint $remoteEndpoint): void
    {
        $this->span->setRemoteEndpoint($remoteEndpoint);
    }

    public function abandon(): void
    {
        $this->span->abandon();
    }

    public function flush(): void
    {
        $this->span->flush();
    }
}
