<div>
    <div class="flex">
        <div class="flex-shrink-0 mr-4">
            <img class="h-10 w-10 rounded-full" src="https://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50"
                 alt="{{ $comment->user->name }}">
        </div>
        <div class="flex-grow">
            <div>
                <a href="#" class="font-medium text-gray-900">{{ $comment->user->name }}</a>
            </div>
            <div class="mt-1 flex-grow w-full">
                @if ($isEditing)
                    <form wire:submit.prevent="editComment">
                        <div>
                            <label for="comment" class="sr-only">Comment body</label>
                            <textarea id="comment" name="comment" rows="3"
                                      class="shadow-sm block w-full focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md @error('editCommentText') border-red-500 @enderror"
                                      placeholder="Write something" wire:model.defer="editCommentText"></textarea>

                            @error('editCommentText')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Edit
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-gray-700">{!! $comment->text !!}</p>
                @endif
            </div>
            <div class="mt-2 space-x-2">
        <span class="text-gray-500 font-medium">
          {{ $comment->created_at->diffForHumans() }}
        </span>
                @auth
                    @if ($comment->isTopLevel())
                        <button wire:click="$toggle('isReplying')" type="button" class="text-gray-900 font-medium">
                            Reply
                        </button>
                    @endif

                    @can('update', $comment)
                        <button wire:click="$toggle('isEditing')" type="button" class="text-gray-900 font-medium">
                            Edit
                        </button>
                    @endcan

                    {{-- @can('destroy', $comment)  --}}
                    <button
                        type="button"
                        class="text-gray-900 font-medium"
                        wire:click="deleteComment"
                    >
                        Delete
                    </button>
                    {{-- @endcan --}}
                @endauth
            </div>

            <div class="my-4 flex space-x-8">
                @foreach($comment->reactions->summary() as $summary)
                    <div wire:click="removeReaction('{{ $summary['reaction'] }}')"
                         class="@if($summary['current_user_reacted']) ? 'bg-blue-200' : '' @endif">
                        {{ $summary['reaction'] }} {{ $summary['count'] }}
                    </div>
                @endforeach
            </div>

            @auth
                <div class="my-4 flex space-x-8">
                    @foreach(config('comments.allowed_reactions') as $reaction)
                        <div class="border-1 border-red-600" wire:click="react('{{ $reaction }}')">{{ $reaction }}</div>
                    @endforeach
                </div>
            @endauth
        </div>
    </div>

    <div class="ml-14 mt-6">
        @if ($isReplying)
            <form wire:submit.prevent="postReply" class="my-4">
                <div>
                    <label for="comment" class="sr-only">Reply body</label>
                    <textarea id="comment" name="comment" rows="3"
                              class="shadow-sm block w-full focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md @error('replyCommentText') border-red-500 @enderror"
                              placeholder="Write something" wire:model.defer="replyCommentText"></textarea>

                    @error('replyCommentText')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Comment
                    </button>
                </div>
            </form>
        @endif

        @foreach ($comment->nestedComments as $comment)
            <livewire:comment :comment="$comment" :key="$comment->id"/>
        @endforeach
    </div>
</div>
