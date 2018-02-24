<?

namespace slowdream\kit_laravel;
use Illuminate\Support\Facades\Facade;

class Kit extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'kit';
    }
}