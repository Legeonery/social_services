<script setup>
import axios from 'axios';
import { onMounted, ref, watchEffect } from 'vue';

const props = defineProps({
    modelValue: Object,
    isEdit: Boolean,
});
const isLoaded = ref(false);
const emit = defineEmits(['submit', 'cancel']);
const today = new Date().toISOString().slice(0, 10);
const errorMessage = ref(null);
const attachedClients = ref([]); // список прикреплённых клиентов
const allClients = ref([]); // список всех доступных клиентов
const absences = ref([]);
const showAbsences = ref(false);

const fetchAbsences = async () => {
    if (!props.modelValue?.id) return;
    try {
        const response = await axios.get(`/users/social-workers/${props.modelValue.id}/absences`);
        absences.value = response.data.absences.map((abs) => {
            const isPast = abs.to < today;
            return {
                ...abs,
                editable: false,
                isPast,
            };
        });
    } catch (error) {
        console.error('Ошибка загрузки отпусков/больничных:', error);
    }
};

const deleteAbsence = async (id) => {
    try {
        await axios.delete(`/absences/${id}`);
        absences.value = absences.value.filter((a) => a.id !== id);
    } catch (e) {
        console.error('Ошибка удаления:', e);
    }
};

//const updateAbsence = async (absence) => {
//    try {
//        await axios.put(`/absences/${absence.id}`, {
//            type: absence.type,
//            from: absence.from,
//            to: absence.to,
//        });
//        absence.editable = false;
//    } catch (e) {
//        console.error('Ошибка обновления:', e);
//    }
//};
onMounted(async () => {
    if (!props.isEdit) {
        isLoaded.value = false;

        try {
            // Инициализируем форму с пустыми значениями
            form.value = {
                lastName: '',
                firstName: '',
                middleName: '',
                phone: '',
                email: '',
                status: 'Активный',
                unavailabilityPeriod: {
                    from: '',
                    to: '',
                },
            };
        } catch (error) {
            console.error('Ошибка при инициализации формы (добавление):', error);
        } finally {
            isLoaded.value = true;
        }
    }
});
const form = ref({
    lastName: '',
    firstName: '',
    middleName: '',
    phone: '',
    email: '',
    status: 'Активный',
    unavailabilityPeriod: {
        from: '',
        to: '',
    },
});

watchEffect(async () => {
    if (props.isEdit && props.modelValue?.id) {
        isLoaded.value = false;
        try {
            const [lastName = '', firstName = '', middleName = ''] = props.modelValue.fullName?.split(' ') || [];
            form.value = {
                lastName,
                firstName,
                middleName,
                phone: props.modelValue.phone || '',
                email: props.modelValue.email || '',
                status: props.modelValue.status || 'Активный',
                unavailabilityPeriod: props.modelValue.unavailabilityPeriod || { from: '', to: '' },
            };

            await fetchAbsences();

            const today = new Date().toISOString().slice(0, 10);
            const current = absences.value.find((a) => a.from <= today && a.to >= today);
            if (current) {
                form.value.status = current.type === 'vacation' ? 'В отпуске' : 'На больничном';
            }

            const response = await axios.get(`/users/social-workers/${props.modelValue.id}/clients`);
            attachedClients.value = response.data.clients.map((c) => ({
                ...c,
                isExisting: true,
                temporary: !!c.temporary,
                period: c.period ?? { from: '', to: '' },
                hasPrimaryWorker: !!c.hasPrimaryWorker,
                absencePeriod: c.absencePeriod || null,
            }));

            const { data } = await axios.get('/users/unassigned-clients', {
                params: { worker_id: props.modelValue.id },
            });
            allClients.value = data.clients;

            attachedClients.value.forEach((attached) => {
                if (!allClients.value.find((c) => c.id === attached.id)) {
                    allClients.value.push({
                        id: attached.id,
                        name: attached.fullName,
                    });
                }
            });
        } catch (error) {
            console.error('Ошибка инициализации формы:', error);
        } finally {
            isLoaded.value = true;
        }
    }
});

const isAbsenceActive = ref(false);

watchEffect(() => {
    const today = new Date().toISOString().slice(0, 10);
    const currentAbsence = absences.value.find((a) => a.from <= today && a.to >= today);

    isAbsenceActive.value = !!currentAbsence;

    if (currentAbsence) {
        form.value.status = currentAbsence.type === 'vacation' ? 'В отпуске' : 'На больничном';
    }
});

watchEffect(() => {
    if (!['На больничном', 'В отпуске'].includes(form.value.status)) {
        form.value.unavailabilityPeriod = { from: '', to: '' };
    }
});

const addClient = () => {
    attachedClients.value.push({
        id: null,
        fullName: '',
        temporary: false,
        period: { from: '', to: '' },
        hasPrimaryWorker: false,
    });
};

const removedClientIds = ref([]);

const removeClient = (index) => {
    const removed = attachedClients.value[index];
    if (removed?.isExisting) {
        removedClientIds.value.push(removed.id);
    }
    attachedClients.value.splice(index, 1);
};

const validateForm = () => {
    if (!form.value.lastName.trim()) {
        errorMessage.value = 'Фамилия обязательна';
        return false;
    }
    if (!form.value.firstName.trim()) {
        errorMessage.value = 'Имя обязательно';
        return false;
    }
    if (!form.value.phone.trim()) {
        errorMessage.value = 'Телефон обязателен';
        return false;
    }
    if (!form.value.email.trim()) {
        errorMessage.value = 'Email обязателен';
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(form.value.email)) {
        errorMessage.value = 'Неверный формат email';
        return false;
    }

    errorMessage.value = null;
    return true;
};

const handleSubmit = async () => {
    if (!validateForm()) return;

    try {
        const payload = {
            ...form.value,
            fullName: `${form.value.lastName} ${form.value.firstName} ${form.value.middleName}`,
            attachedClients: attachedClients.value.map((client) => ({
                client_id: client.id,
                temporary: client.temporary,
                period: client.temporary ? client.period : null,
            })),
            removedClients: removedClientIds.value,
        };

        if (!['На больничном', 'В отпуске'].includes(payload.status)) {
            delete payload.unavailabilityPeriod;
        }

        let response;
        if (props.isEdit && props.modelValue?.id) {
            response = await axios.put(`/users/social-workers/${props.modelValue.id}`, payload);
        } else {
            response = await axios.post('/users/social-workers', payload);
        }

        if (response.data.success) {
            emit('submit', response.data.user);
            emit('cancel');
        } else {
            errorMessage.value = response.data.message || 'Ошибка при сохранении';
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            errorMessage.value = Object.values(error.response.data.errors).flat().join(', ');
        } else {
            errorMessage.value = error.response?.data?.message || 'Ошибка сервера';
        }
    }
};

const cancel = () => {
    emit('cancel');
};
watchEffect(() => {
    attachedClients.value.forEach((client) => {
        if (!client.isExisting && client.id) {
            const found = allClients.value.find((c) => c.id === client.id);

            if (found) {
                client.hasPrimaryWorker = found.hasPrimaryWorker;

                // Если есть основной соц. работник, то временно и disabled
                if (found.hasPrimaryWorker) {
                    client.temporary = true;
                }
            }
        }
    });
});
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div
            class="w-full max-w-xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800"
            style="max-height: 80vh; scrollbar-width: none"
        >
            <h2 class="mb-4 text-xl font-semibold dark:text-white">
                {{ isEdit ? 'Редактировать соц. работника' : 'Добавить соц. работника' }}
            </h2>

            <form v-if="isLoaded" @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium dark:text-white">Фамилия <span class="text-red-500">*</span> </label>
                    <input v-model="form.lastName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Имя <span class="text-red-500">*</span> </label>
                    <input v-model="form.firstName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Отчество</label>
                    <input v-model="form.middleName" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Телефон <span class="text-red-500">*</span> </label>
                    <input v-model="form.phone" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-white">Email <span class="text-red-500">*</span> </label>
                    <input v-model="form.email" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" type="email" />
                </div>

                <div v-if="isEdit">
                    <label class="block text-sm font-medium dark:text-white">Статус</label>
                    <select v-model="form.status" class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white" :disabled="isAbsenceActive">
                        <option>Активный</option>
                        <option>В отпуске</option>
                        <option>На больничном</option>
                        <option>Уволенный</option>
                    </select>
                </div>

                <div v-if="['В отпуске', 'На больничном'].includes(form.status) && !isAbsenceActive">
                    <label class="block text-sm font-medium dark:text-white">Период недоступности</label>
                    <div class="flex gap-2">
                        <input
                            type="date"
                            v-model="form.unavailabilityPeriod.from"
                            :min="today"
                            class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white"
                        />
                        <input
                            type="date"
                            v-model="form.unavailabilityPeriod.to"
                            :min="form.unavailabilityPeriod.from || today"
                            class="w-full rounded border p-2 dark:bg-gray-700 dark:text-white"
                        />
                    </div>
                </div>
                <div v-if="isEdit" class="mt-6">
                    <button type="button" @click="showAbsences = !showAbsences" class="text-blue-400 underline">
                        {{ showAbsences ? 'Скрыть' : 'Показать' }} отпуска и больничные
                    </button>

                    <div v-if="showAbsences" class="mt-4 space-y-4 rounded bg-gray-100 p-4 dark:bg-gray-700 dark:text-white">
                        <div v-for="absence in absences" :key="absence.id" class="flex flex-wrap items-center gap-2">
                            <span class="min-w-[100px] font-semibold">
                                {{ absence.type === 'vacation' ? 'Отпуск' : 'Больничный' }}
                            </span>

                            <span>{{ absence.from }}</span>

                            <input
                                type="date"
                                v-model="absence.to"
                                :min="today"
                                :disabled="!absence.editable || absence.isPast"
                                class="rounded p-1 dark:bg-gray-800"
                            />

                            <button
                                v-if="!absence.editable && !absence.isPast"
                                @click="absence.editable = true"
                                type="button"
                                class="text-yellow-400"
                            >
                                ✏️
                            </button>
                            <button @click="deleteAbsence(absence.id)" type="button" class="text-red-500">🗑️</button>
                        </div>
                        <div v-if="absences.length === 0" class="text-sm text-gray-400">Нет записей</div>
                        <div v-if="!isAbsenceActive" class="text-sm text-green-300">
                            Можно добавить новый отпуск/больничный в основном контроллере (через статус)
                        </div>
                        <div v-else class="text-sm text-yellow-400">На текущую дату уже есть активный период. Добавление временно недоступно.</div>
                    </div>
                </div>
                <div v-if="isEdit" class="mt-8 rounded bg-gray-700 p-4 text-white">
                    <h3 class="mb-2 text-lg font-bold">Закреплённые клиенты</h3>

                    <div v-for="(client, index) in attachedClients" :key="index" class="mb-4 flex flex-wrap items-center gap-2 sm:gap-4">
                        <!-- Клиент -->
                        <div class="min-w-[180px] flex-1">
                            <div v-if="client.isExisting" class="w-full truncate rounded bg-gray-600 p-2 text-white">
                                {{ allClients.find((c) => c.id === client.id)?.name || 'Неизвестный клиент' }}
                            </div>
                            <select v-else v-model="client.id" class="w-full rounded p-2 dark:bg-gray-600 dark:text-white">
                                <option disabled value="">Выберите клиента</option>
                                <option v-for="c in allClients" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>

                        <!-- Временный клиент -->
                        <div class="flex items-center gap-2">
                            <input type="checkbox" v-model="client.temporary" :disabled="isAbsenceActive || client.hasPrimaryWorker" />
                            <span :class="{ 'opacity-50': isAbsenceActive || client.hasPrimaryWorker }">Временный клиент</span>
                            <div class="text-sm text-yellow-300" v-if="client.hasPrimaryWorker">
                                У клиента уже есть основной соц. работник. Прикрепление возможно только временно.
                            </div>
                        </div>

                        <!-- Период -->
                        <template v-if="client.temporary">
                            <input
                                type="date"
                                v-model="client.period.from"
                                :min="client.absencePeriod?.from || today"
                                :max="client.absencePeriod?.to || null"
                                class="rounded p-2 dark:bg-gray-600 dark:text-white"
                            />
                            <input
                                type="date"
                                v-model="client.period.to"
                                :min="client.period.from || client.absencePeriod?.from || today"
                                :max="client.absencePeriod?.to || null"
                                class="rounded p-2 dark:bg-gray-600 dark:text-white"
                            />
                        </template>

                        <!-- Кнопка удаления -->
                        <button type="button" @click="removeClient(index)" class="text-lg text-red-500">🗑️</button>
                    </div>

                    <button type="button" @click="addClient" :disabled="isAbsenceActive" class="text-blue-300 underline disabled:opacity-50">
                        + Добавить клиента
                    </button>
                </div>
                <div v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="cancel" class="rounded bg-gray-200 px-4 py-2 dark:bg-gray-600 dark:text-white">Отмена</button>
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        {{ isEdit ? 'Сохранить' : 'Добавить' }}
                    </button>
                </div>
            </form>
            <div v-else class="text-white">Загрузка данных...</div>
        </div>
    </div>
</template>
<style scoped>
::-webkit-scrollbar {
    display: none;
}
</style>
