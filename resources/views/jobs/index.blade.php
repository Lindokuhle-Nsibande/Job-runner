@extends('layouts.app')

@section('content')
<div class="container max-w-7xl m-auto align-middle p-5">
    <h1 class="mb-4 text-3xl">Background Jobs</h1>
    <table class="table-fixed w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th class="px-6 py-3">ID</th>
                <th scope="col" class="px-6 py-3">Class</th>
                <th scope="col" class="px-6 py-3">Method</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Attempts</th>
                <th scope="col" class="px-6 py-3">Created At</th>
                <th scope="col" class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if ($jobs->isEmpty())
                <tr>
                    <td colspan="7" class="text-center py-4">No Processes</td>
                </tr>
            @else
                @foreach($jobs as $job)
                <tr>
                    <td class="px-6 py-4">{{ $job->id }}</td>
                    <td class="px-6 py-4">{{ $job->class }}</td>
                    <td class="px-6 py-4">{{ $job->method }}</td>
                    <td class="px-6 py-4">{{ $job->status }}</td>
                    <td class="px-6 py-4">{{ $job->attempts }}/{{ $job->max_attempts }}</td>
                    <td class="px-6 py-4">{{ $job->created_at }}</td>
                    <td class="px-6 py-4">
                        @if($job->status === 'processing')
                            <form method="POST" action="{{ route('jobs.destroy', $job->id) }}">
                                @csrf
                                @method('DELETE')
                                <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-xs">
                                    Cancel
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="mt-4">
        {{ $jobs->links() }}
    </div>
</div>
@endsection
