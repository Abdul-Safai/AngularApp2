import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  email = '';
  password = '';
  errorMessage = '';
  lockoutRemaining = 0;
  showWarningBox = false;
  timer: any;

  constructor(private http: HttpClient, private router: Router) {}

  login(): void {
    this.errorMessage = '';
    const user = { email: this.email, password: this.password };

    this.http.post<any>('http://localhost/AngularApp2/angularapp_api/login_user.php', user).subscribe({
      next: (res) => {
        const ok = res?.success === true || res?.message === 'Login successful';
        if (ok) {
          localStorage.setItem('user', JSON.stringify(res.user ?? {}));
          this.router.navigate(['/home']);
        } else {
          this.errorMessage = res?.error || 'Login failed. Please try again.';
          this.showWarningBox = true;
        }
      },
      error: (err) => {
        if (err.status === 403 && err.error?.remainingSeconds) {
          this.startCountdown(err.error.remainingSeconds);
          this.errorMessage = err.error?.error || 'Too many failed attempts.';
          this.showWarningBox = true;
        } else {
          this.errorMessage = err?.error?.error || 'Invalid email or password.';
          this.showWarningBox = true;
        }
      }
    });
  }

  startCountdown(seconds: number) {
    this.lockoutRemaining = seconds;
    this.showWarningBox = true;
    clearInterval(this.timer);

    this.timer = setInterval(() => {
      if (this.lockoutRemaining > 0) {
        this.lockoutRemaining--;
      } else {
        clearInterval(this.timer);
        this.lockoutRemaining = 0;
        this.errorMessage = '';
        this.showWarningBox = false; // ✅ Hide entire box
      }
    }, 1000);
  }

  formatTime(seconds: number): string {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
  }
}
