<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
        Notification::fake();
    }

    public function test_process_mock_payment_success_creates_payment_and_confirms_booking(): void
    {
        $booking = Booking::factory()->create([
            'status' => BookingStatus::Pending,
            'quantity' => 2,
        ]);
        $booking->ticket->update(['price' => 50.00]);
        $expectedAmount = 50.00 * 2;

        $result = $this->paymentService->processMockPayment($booking, true);

        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Payment::class, $result['payment']);
        $this->assertSame(PaymentStatus::Success, $result['payment']->status);
        $this->assertEquals($expectedAmount, (float) $result['payment']->amount);

        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
    }

    public function test_process_mock_payment_failure_creates_failed_payment_and_keeps_booking_pending(): void
    {
        $booking = Booking::factory()->create([
            'status' => BookingStatus::Pending,
            'quantity' => 1,
        ]);
        $booking->ticket->update(['price' => 30.00]);

        $result = $this->paymentService->processMockPayment($booking, false);

        $this->assertFalse($result['success']);
        $this->assertSame(PaymentStatus::Failed, $result['payment']->status);
        $this->assertSame('30.00', (string) $result['payment']->amount);

        $booking->refresh();
        $this->assertSame(BookingStatus::Pending, $booking->status);
    }

    public function test_process_mock_payment_updates_existing_payment_record(): void
    {
        $booking = Booking::factory()->create(['status' => BookingStatus::Pending]);
        $existingPayment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 0,
            'status' => PaymentStatus::Failed,
        ]);

        $result = $this->paymentService->processMockPayment($booking, true);

        $this->assertSame($existingPayment->id, $result['payment']->id);
        $this->assertSame(PaymentStatus::Success, $result['payment']->status);
    }

    public function test_simulate_success_returns_payment_with_success_status(): void
    {
        $booking = Booking::factory()->create(['status' => BookingStatus::Pending]);

        $payment = $this->paymentService->simulateSuccess($booking);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame(PaymentStatus::Success, $payment->status);
    }

    public function test_simulate_failure_returns_payment_with_failed_status(): void
    {
        $booking = Booking::factory()->create(['status' => BookingStatus::Pending]);

        $payment = $this->paymentService->simulateFailure($booking);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame(PaymentStatus::Failed, $payment->status);
    }
}
