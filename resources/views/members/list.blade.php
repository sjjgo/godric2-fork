<x-layout>
    <x-slot:title>Member List</x-slot:title>

    <p>Click on names to update participation in current campaigns.</p>

    <p>Name, contact and affiliation data is derived from national data sources - please send corrections to national in the usual way.</p>
    
    <table class="datatable" data-order='[[1, "asc"]]' data-length-menu='[[25,100,-1],[25,100,"All"]]'>
	<thead>
	    <tr>
		<th>Membership ID</th>
		<th>Name</th>
		<th>Email</th>
		<th>Phone</th>
		<th>Department</th>
		<th>Job Type</th>
		<th>Membership Type</th>
		<th>Voter?</th>
		<th>Past Campaigns</th>
		@foreach ($campaigns as $campaign)
		    <th>{{ $campaign->name }}</th>
		@endforeach
	    </tr>
	</thead>
	<tbody>
	    @foreach ($members as $member)
		<tr>
		    <td>{{ $member->membership }}</td>
		    <td data-sort="{{$member->lastname}} {{$member->firstname}}"><a href="{{ route('members.edit', $member->id) }}">{{ $member->firstname }} {{ $member->lastname }}</a></td>
		    <td>{{ $member->email }}</td>
		    <td>{{ $member->mobile }}</td>
		    <td>{{ $member->department }}</td>
		    <td>{{ $member->jobtype }}</td>
		    <td>{{ $member->membertype }}</td>
		    <td>{{ $member->voter ? "Yes" : "No" }}</td>
		    <td>
			@foreach ($pastcampaigns as $pc) 
			    @if ($pc->participation($member) == "yes")
				<span title="{{ $pc->name }}">&#x2714;</span>
			    @endif
			@endforeach
		    </td>
		    @foreach ($campaigns as $campaign)
			<td>{{ $campaign->participation($member) }}</td>
		    @endforeach
		</tr>
	    @endforeach
	</tbody>
    </table>

    <h2>Download data</h2>

    <p>Downloaded copies of data must be used in accordance with data protection policies, and deleted once no longer required.</p>
	
    <ul>
	<li><a href="{{ route('members.export') }}?format=email&amp;full=1">Export Full Email List</a></li>
	<li><a href="{{ route('members.export') }}?format=email">Export Campaign Email List</a> (excludes those who have already participated)</li>
	<li><a href="{{ route('members.export') }}?format=phone&amp;full=1">Export Full Phone List</a></li>
	<li><a href="{{ route('members.export') }}?format=phone">Export Campaign Phone List</a> (excludes those who have already participated)</li>
	<li><a href="{{ route('members.export') }}?format=thrutext">Export Campaign Thrutext List</a> (excludes those who have already participated, mobiles only)</li>
	<li><a href="{{ route('members.export') }}?format=rep">Download Rep CSV</a> (all campaigns)</li>
    </ul>
	
</x-layout>
